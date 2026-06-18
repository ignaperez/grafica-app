<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\Configuracion;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;

class FacturaPdfService
{
    /**
     * Genera el PDF A4 de la factura con paginación nativa de mPDF:
     * - Encabezado (emisor + letra + comprobante + cliente) repetido en cada hoja.
     * - Pie (CAE + QR + código de barras + N° de hoja X/Y) repetido en cada hoja.
     * - Total + transparencia + monto en letras: SOLO al final (última hoja).
     */
    public function generar(Factura $factura, bool $preview = false): Mpdf
    {
        $factura->loadMissing(['cliente', 'items']);
        $data = $this->buildData($factura);
        $data['preview'] = $preview;

        // tempDir escribible para mPDF
        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 7,
            'margin_right'  => 7,
            'margin_top'    => 64,   // reserva para el encabezado repetido (más alto)
            'margin_bottom' => 38,   // reserva para el pie repetido (QR + CAE)
            'margin_header' => 5,
            'margin_footer' => 5,
            'default_font'  => 'dejavusans',
            'tempDir'       => $tempDir,
        ]);

        $mpdf->SetTitle($preview ? 'Previsualización' : $data['fileNombre']);
        $mpdf->showImageErrors = false;

        // Marca de agua en la previa (no se emite, no tiene valor fiscal).
        if ($preview) {
            $mpdf->SetWatermarkText('PREVISUALIZACIÓN');
            $mpdf->showWatermarkText = true;
            $mpdf->watermarkTextAlpha = 0.08;
        }

        // CSS compartido (se aplica también a header/footer)
        $css = view('facturas.pdf.styles')->render();
        $mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);

        // Encabezado y pie repetidos en TODA hoja
        $mpdf->SetHTMLHeader(view('facturas.pdf.header', $data)->render());
        $mpdf->SetHTMLFooter(view('facturas.pdf.footer', $data)->render());

        // Cuerpo: tabla de ítems (mPDF pagina sola y repite el thead)
        $mpdf->WriteHTML(view('facturas.pdf.body', $data)->render(), HTMLParserMode::HTML_BODY);

        // Cierre (Observaciones + Total + SEUO): SIEMPRE al fondo de la última
        // hoja, sin importar la cantidad de ítems. Medimos su alto real y bajamos
        // el cursor para anclarlo justo arriba del pie.
        $cierreHtml = view('facturas.pdf.cierre', $data)->render();
        $cierreH    = $this->alturaMm($cierreHtml, $css, $tempDir);
        $limiteY    = $mpdf->h - $mpdf->bMargin;          // inicio de la zona del pie
        $targetY    = $limiteY - $cierreH - 3;            // 3mm de aire sobre el pie
        if ($targetY > $mpdf->y) {
            $mpdf->SetY($targetY);                        // empujar al fondo
        }
        $mpdf->WriteHTML($cierreHtml, HTMLParserMode::HTML_BODY);

        return $mpdf;
    }

    /**
     * Alto (mm) que ocupa un fragmento HTML al ancho del cuerpo (A4 menos
     * márgenes 7/7), renderizándolo en una instancia mPDF descartable.
     */
    private function alturaMm(string $html, string $css, string $tempDir): float
    {
        $m = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 7,
            'margin_right'  => 7,
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'default_font'  => 'dejavusans',
            'tempDir'       => $tempDir,
        ]);
        $m->showImageErrors = false;
        $m->WriteHTML($css, HTMLParserMode::HEADER_CSS);
        $y0 = $m->y;
        $m->WriteHTML($html, HTMLParserMode::HTML_BODY);
        return max(0, $m->y - $y0);
    }

    // ── Datos calculados (portado de facturas/print.blade) ──────────────────

    private function buildData(Factura $factura): array
    {
        $empresa = Configuracion::empresa();

        $tipo            = (int) $factura->tipo;
        $esNC            = in_array($tipo, [3, 8, 13]);
        $ivaDiscriminado = in_array($tipo, [1, 3]); // solo Factura/NC A

        $letra     = match ($tipo) { 1, 3 => 'A', 6, 8 => 'B', 11, 13 => 'C', default => '?' };
        $codTipo   = str_pad((string) $tipo, 2, '0', STR_PAD_LEFT);
        $tipoLabel = $esNC ? 'NOTA DE CRÉDITO' : 'FACTURA';

        $condIvaShort = match ($factura->cliente->condicion_iva ?? '') {
            'responsable_inscripto' => 'RESP. INSCRIPTO',
            'monotributo'           => 'MONOTRIBUTISTA',
            'exento'                => 'EXENTO',
            'consumidor_final'      => 'CONSUMIDOR FINAL',
            default                 => strtoupper($factura->cliente->condicion_iva ?? '—'),
        };

        $conceptoLabel = match ($factura->concepto) {
            1 => 'Productos', 2 => 'Servicios', 3 => 'Productos y Servicios', default => '—',
        };

        $docTipoLabel = match ((int) $factura->doc_tipo) {
            80 => 'CUIT', 96 => 'DNI', 99 => 'Consumidor Final', default => 'Doc.',
        };

        // ── Desglose IVA (solo A) ──
        $desgloseIva = [];
        if ($ivaDiscriminado) {
            foreach ($factura->items as $item) {
                $ali    = (float) $item->alicuota_iva;
                $factor = $ali > 0 ? (1 + $ali / 100) : 1;
                $base   = round((float) $item->subtotal / $factor, 2);
                $iva    = round((float) $item->subtotal - $base, 2);
                $key    = number_format($ali, 2, '.', '');
                if (!isset($desgloseIva[$key])) $desgloseIva[$key] = ['ali' => $ali, 'base' => 0, 'iva' => 0];
                $desgloseIva[$key]['base'] = round($desgloseIva[$key]['base'] + $base, 2);
                $desgloseIva[$key]['iva']  = round($desgloseIva[$key]['iva']  + $iva,  2);
            }
            ksort($desgloseIva);
        }

        $ivaContenido = round((float) $factura->imp_iva, 2);

        return [
            'factura'         => $factura,
            'empresa'         => $empresa,
            'tipo'            => $tipo,
            'esNC'            => $esNC,
            'ivaDiscriminado' => $ivaDiscriminado,
            'letra'           => $letra,
            'codTipo'         => $codTipo,
            'tipoLabel'       => $tipoLabel,
            'condIvaShort'    => $condIvaShort,
            'conceptoLabel'   => $conceptoLabel,
            'docTipoLabel'    => $docTipoLabel,
            'desgloseIva'     => $desgloseIva,
            'ivaContenido'    => $ivaContenido,
            'montoLetras'     => $this->montoEnLetras((float) $factura->imp_total),
            'cuitFmt'         => $this->fmtCuit($empresa['cuit'] ?? ''),
            'logoData'        => $this->logoDataUri($empresa['logo'] ?? ''),
            'qrData'          => $factura->tieneCAE() ? $this->qrDataUri($factura->qrUrl()) : null,
            'fileNombre'      => $this->fileNombre($factura),
        ];
    }

    // ── QR (PNG embebido, generado local — sin depender de servicios externos) ──

    private function qrDataUri(string $contenido): string
    {
        $result = (new Builder(
            writer: new PngWriter(),
            data: $contenido,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 200,
            margin: 0,
        ))->build();

        return $result->getDataUri();
    }

    // ── Logo como data URI (mPDF lee mejor data URIs que URLs remotas) ──

    private function logoDataUri(string $rutaRelativa): ?string
    {
        if (!$rutaRelativa) return null;
        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($rutaRelativa)) return null;
            $bin  = $disk->get($rutaRelativa);
            $mime = $disk->mimeType($rutaRelativa) ?: 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode($bin);
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ── Monto en letras ─────────────────────────────────────────────────────

    private function montoEnLetras(float $total): string
    {
        $ent = (int) $total;
        $cts = (int) round(($total - $ent) * 100);
        $txt = ($ent > 0 ? $this->numeroALetras($ent) : 'Cero') . ' Pesos';
        if ($cts > 0) $txt .= ' con ' . sprintf('%02d', $cts) . '/100';
        return $txt;
    }

    private function numeroALetras(int $n): string
    {
        $uni = ['', 'Un', 'Dos', 'Tres', 'Cuatro', 'Cinco', 'Seis', 'Siete', 'Ocho', 'Nueve',
                'Diez', 'Once', 'Doce', 'Trece', 'Catorce', 'Quince'];
        if ($n <= 15)  return $uni[$n];
        if ($n <= 19)  return 'Dieci' . mb_strtolower($this->numeroALetras($n - 10));
        if ($n === 20) return 'Veinte';
        if ($n <= 29)  return 'Veinti' . mb_strtolower($this->numeroALetras($n - 20));
        $dec = ['', '', 'Treinta', 'Cuarenta', 'Cincuenta', 'Sesenta', 'Setenta', 'Ochenta', 'Noventa'];
        if ($n < 100)  return $dec[(int) ($n / 10)] . ($n % 10 ? ' y ' . $this->numeroALetras($n % 10) : '');
        if ($n === 100) return 'Cien';
        $cen = ['', 'Ciento', 'Doscientos', 'Trescientos', 'Cuatrocientos', 'Quinientos',
                'Seiscientos', 'Setecientos', 'Ochocientos', 'Novecientos'];
        if ($n < 1000)    return $cen[(int) ($n / 100)] . ($n % 100 ? ' ' . $this->numeroALetras($n % 100) : '');
        if ($n < 2000)    return 'Mil' . ($n % 1000 ? ' ' . $this->numeroALetras($n % 1000) : '');
        if ($n < 1000000) return $this->numeroALetras((int) ($n / 1000)) . ' Mil' . ($n % 1000 ? ' ' . $this->numeroALetras($n % 1000) : '');
        if ($n < 2000000) return 'Un Millón' . ($n % 1000000 ? ' ' . $this->numeroALetras($n % 1000000) : '');
        return $this->numeroALetras((int) ($n / 1000000)) . ' Millones' . ($n % 1000000 ? ' ' . $this->numeroALetras($n % 1000000) : '');
    }

    // ── CUIT formateado y nombre de archivo ─────────────────────────────────

    private function fmtCuit(string $c): string
    {
        $c = preg_replace('/\D/', '', $c);
        return strlen($c) === 11
            ? substr($c, 0, 2) . '-' . substr($c, 2, 8) . '-' . substr($c, 10)
            : ($c ?: '—');
    }

    public function nombreArchivo(Factura $factura): string
    {
        return $this->fileNombre($factura);
    }

    private function fileNombre(Factura $factura): string
    {
        $san = function (string $s, int $max): string {
            $s = Str::ascii($s);
            $s = strtoupper($s);
            $s = preg_replace('/\s+/', '_', trim($s));
            $s = preg_replace('/[^A-Z0-9_]/', '', $s);
            $s = preg_replace('/_+/', '_', $s);
            return substr($s, 0, $max);
        };

        return implode('_', array_filter([
            'PV'  . str_pad((string) $factura->punto_venta, 3, '0', STR_PAD_LEFT),
            'NRO' . str_pad((string) $factura->numero,      4, '0', STR_PAD_LEFT),
            $san($factura->cliente->nombre ?? '', 26),
            $san($factura->observaciones   ?? '', 6),
        ]));
    }
}
