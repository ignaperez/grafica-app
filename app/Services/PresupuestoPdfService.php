<?php

namespace App\Services;

use App\Models\Presupuesto;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;

/**
 * PDF A4 del presupuesto, generado EN EL SERVIDOR (mismo esquema que
 * FacturaPdfService / RemitoPdfService).
 *
 * Reemplaza al window.print() sobre presupuestos/print.blade.php, que repetía
 * encabezado y pie con position:fixed y reservaba el espacio con márgenes
 * estimados en píxeles. Un elemento fijo se pinta en TODAS las hojas, pero ese
 * margen solo reserva lugar al principio y al final del documento entero: de la
 * hoja 2 en adelante el encabezado tapaba las primeras filas de la tabla y el
 * pie se dibujaba encima del total.
 *
 * Acá el encabezado y el pie son header/footer nativos de mPDF (se repiten de
 * verdad y el cuerpo nunca entra en su banda), la paginación es real y el total
 * cae SOLO en la última hoja, apoyado justo sobre el pie.
 */
class PresupuestoPdfService
{
    /** Alto reservado para el encabezado repetido (mm). */
    private const MARGIN_TOP = 52;

    /** Alto reservado para el pie repetido (mm). */
    private const MARGIN_BOTTOM = 20;

    /** Márgenes laterales (mm). */
    private const MARGIN_X = 14;

    public function generar(Presupuesto $presupuesto): Mpdf
    {
        $presupuesto->loadMissing(['cliente', 'items']);
        $data = $this->buildData($presupuesto);

        $tempDir = storage_path('app/mpdf');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => self::MARGIN_X,
            'margin_right'  => self::MARGIN_X,
            'margin_top'    => self::MARGIN_TOP,
            'margin_bottom' => self::MARGIN_BOTTOM,
            'margin_header' => 8,
            'margin_footer' => 6,
            'default_font'  => 'dejavusans',
            'tempDir'       => $tempDir,
        ]);

        $mpdf->SetTitle($data['fileNombre']);
        $mpdf->showImageErrors = false;

        $css = view('presupuestos.pdf.styles')->render();
        $mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);
        $mpdf->SetHTMLHeader(view('presupuestos.pdf.header', $data)->render());
        $mpdf->SetHTMLFooter(view('presupuestos.pdf.footer', $data)->render());

        // Cuerpo: cliente + tabla de ítems. mPDF pagina solo y repite el <thead>.
        $mpdf->WriteHTML(view('presupuestos.pdf.body', $data)->render(), HTMLParserMode::HTML_BODY);

        // Cierre (total + condiciones): SIEMPRE al fondo de la última hoja, sin
        // importar la cantidad de ítems. Se mide su alto con un mPDF descartable
        // y se baja el cursor para apoyarlo justo arriba del pie. Si los ítems ya
        // llenaron la hoja, fluye normal y se va a la siguiente.
        $cierreHtml = view('presupuestos.pdf.cierre', $data)->render();
        $cierreH    = $this->alturaMm($cierreHtml, $css, $tempDir);
        $limiteY    = $mpdf->h - $mpdf->bMargin;
        $targetY    = $limiteY - $cierreH - 3;
        if ($targetY > $mpdf->y) {
            $mpdf->SetY($targetY);
        }
        $mpdf->WriteHTML($cierreHtml, HTMLParserMode::HTML_BODY);

        return $mpdf;
    }

    /**
     * Alto (mm) de un fragmento HTML al ancho del cuerpo (A4 menos los márgenes
     * laterales), renderizado en una instancia mPDF descartable.
     */
    private function alturaMm(string $html, string $css, string $tempDir): float
    {
        $m = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => self::MARGIN_X,
            'margin_right'  => self::MARGIN_X,
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

    public function nombreArchivo(Presupuesto $presupuesto): string
    {
        return $this->fileNombre($presupuesto);
    }

    // ── Datos calculados ────────────────────────────────────────────────────

    private function buildData(Presupuesto $presupuesto): array
    {
        $emp = Configuracion::empresa();
        $cli = $presupuesto->cliente;

        return [
            'presupuesto' => $presupuesto,
            'cli'         => $cli,
            'empresa'     => $emp['nombre_factura'] ?: config('app.name'),
            'owner'       => $emp['propietario'] ?? '',
            'cuit'        => $emp['cuit'] ?? '',
            'dir'         => $emp['direccion'] ?? '',
            'tel'         => $emp['telefono'] ?? '',
            'email'       => $emp['email'] ?? '',
            'iibb'        => $emp['iibb'] ?? '',
            'inicio'      => $emp['inicio_actividades'] ?? '',
            'empIva'      => $this->ivaLabel($emp['condicion_iva'] ?? ''),
            'cliIva'      => $this->ivaLabel($cli->condicion_iva ?? ''),
            // El logo va EMBEBIDO en base64, no por Storage::url(): en contexto
            // tenant los archivos viven en storage/tenant{id}/ y public/storage
            // apunta al storage central, así que la URL da 404 (mismo gotcha que
            // las fotos de fichada). Y mPDF tampoco podría descargarla.
            'logoData'    => $this->logoDataUri($emp['logo'] ?? ''),
            'fileNombre'  => $this->fileNombre($presupuesto),
        ];
    }

    private function ivaLabel(?string $c): string
    {
        return match ($c) {
            'responsable_inscripto' => 'Responsable Inscripto',
            'monotributo'           => 'Responsable Monotributo',
            'exento'                => 'IVA Exento',
            'consumidor_final'      => 'Consumidor Final',
            default                 => $c ? ucfirst(str_replace('_', ' ', $c)) : '',
        };
    }

    private function logoDataUri(string $rutaRelativa): ?string
    {
        if (! $rutaRelativa) {
            return null;
        }

        try {
            $disk = Storage::disk('public');
            if (! $disk->exists($rutaRelativa)) {
                return null;
            }
            $bin  = $disk->get($rutaRelativa);
            $mime = $disk->mimeType($rutaRelativa) ?: 'image/png';

            return 'data:' . $mime . ';base64,' . base64_encode($bin);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** P0001_CLIENTE_OBS — mismo criterio que la factura y que el print viejo. */
    private function fileNombre(Presupuesto $presupuesto): string
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
            'P' . str_pad((string) $presupuesto->numero, 4, '0', STR_PAD_LEFT),
            $san($presupuesto->cliente->nombre ?? '', 26),
            $san($presupuesto->observaciones ?? '', 5),
        ]));
    }
}
