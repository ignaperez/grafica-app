<?php

namespace App\Services;

use App\Models\Remito;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;

class RemitoPdfService
{
    /**
     * PDF A4 del remito con el MISMO formato que la factura (encabezado + pie
     * repetidos en cada hoja, paginación nativa, N° de hoja X/Y).
     * Diferencias vs factura: sin precios ni total, sin QR/CAE; el pie lleva el
     * código propio del remito (CAI papel, autorización electrónica, o nada si
     * es interno). Observaciones + "Recibí conforme" caen solo en la última hoja.
     */
    public function generar(Remito $remito): Mpdf
    {
        $remito->loadMissing(['cliente', 'items', 'remitoCai', 'presupuesto', 'factura']);
        $data = $this->buildData($remito);

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 7,
            'margin_right'  => 7,
            'margin_top'    => 50,
            'margin_bottom' => 45,
            'margin_header' => 5,
            'margin_footer' => 5,
            'default_font'  => 'dejavusans',
            'tempDir'       => $tempDir,
        ]);

        $mpdf->SetTitle($data['fileNombre']);
        $mpdf->showImageErrors = false;

        $mpdf->WriteHTML(view('remitos.pdf.styles')->render(), HTMLParserMode::HEADER_CSS);
        $mpdf->SetHTMLHeader(view('remitos.pdf.header', $data)->render());
        $mpdf->SetHTMLFooter(view('remitos.pdf.footer', $data)->render());
        $mpdf->WriteHTML(view('remitos.pdf.body', $data)->render(), HTMLParserMode::HTML_BODY);

        return $mpdf;
    }

    public function nombreArchivo(Remito $remito): string
    {
        return $this->fileNombre($remito);
    }

    // ── Datos calculados ────────────────────────────────────────────────────

    private function buildData(Remito $remito): array
    {
        $empresa = Configuracion::empresa();

        // Código fiscal del remito (según su tipo) para el pie + barcode
        $codTipoFiscal = null; // 'electronico' | 'cai' | null (interno)
        $codigoBarras  = null;
        $cai           = $remito->remitoCai;

        if ($remito->tieneAutorizacion()) {
            $codTipoFiscal = 'electronico';
            $codigoBarras  = preg_replace('/\D/', '', (string) $remito->cod_autorizacion) ?: null;
        } elseif ($remito->tieneCai() && $cai) {
            $codTipoFiscal = 'cai';
            $codigoBarras  = preg_replace('/\D/', '', (string) $cai->codigo) ?: null;
        }

        // I25 (Interleaved 2of5) requiere longitud PAR: anteponer 0 si hace falta
        if ($codigoBarras && strlen($codigoBarras) % 2 !== 0) {
            $codigoBarras = '0' . $codigoBarras;
        }

        return [
            'remito'        => $remito,
            'empresa'       => $empresa,
            'cai'           => $cai,
            'letra'         => 'R',
            'tipoLabel'     => 'REMITO',
            'codTipoFiscal' => $codTipoFiscal,
            'codigoBarras'  => $codigoBarras,
            'cuitFmt'       => $this->fmtCuit($empresa['cuit'] ?? ''),
            'logoData'      => $this->logoDataUri($empresa['logo'] ?? ''),
            'fileNombre'    => $this->fileNombre($remito),
        ];
    }

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

    private function fmtCuit(string $c): string
    {
        $c = preg_replace('/\D/', '', $c);
        return strlen($c) === 11
            ? substr($c, 0, 2) . '-' . substr($c, 2, 8) . '-' . substr($c, 10)
            : ($c ?: '—');
    }

    private function fileNombre(Remito $remito): string
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
            'REMITO',
            $san($remito->numeroFormateado(), 16),
            $san($remito->cliente->nombre ?? '', 26),
        ]));
    }
}
