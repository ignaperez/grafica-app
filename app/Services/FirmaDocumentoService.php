<?php

namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Estampa una firma PNG (con transparencia) sobre una orden de compra y
 * devuelve el PDF resultante como string.
 *
 * - OC en PDF  → se importa con mPDF (FPDI) conservando las páginas originales
 *   vectoriales; la firma se superpone SOLO en la página elegida.
 * - OC en imagen (jpg/png) → se arma un PDF de una página del tamaño de la
 *   imagen, con la imagen de fondo + la firma encima.
 *
 * Las coordenadas llegan como FRACCIONES (0..1) de la página que el usuario vio
 * en el navegador: x/y = esquina sup-izq de la firma, w = ancho. El alto se
 * deduce del aspecto del PNG (alto 0 en mPDF = proporcional).
 */
class FirmaDocumentoService
{
    private string $tmp;

    public function __construct()
    {
        $this->tmp = storage_path('app/mpdf');
        if (! is_dir($this->tmp)) {
            @mkdir($this->tmp, 0775, true);
        }
    }

    /**
     * @param  string  $ocPath    ruta al archivo de la OC (pdf/jpg/png)
     * @param  string  $firmaPng  ruta al PNG de la firma
     * @param  int     $pagina    página donde va la firma (1-based)
     * @param  float   $xFrac     0..1 (borde izquierdo de la firma)
     * @param  float   $yFrac     0..1 (borde superior de la firma)
     * @param  float   $wFrac     0..1 (ancho de la firma, relativo al ancho de la hoja)
     */
    public function firmar(string $ocPath, string $firmaPng, int $pagina, float $xFrac, float $yFrac, float $wFrac): string
    {
        $esPdf = strtolower(pathinfo($ocPath, PATHINFO_EXTENSION)) === 'pdf'
            || str_starts_with((string) @file_get_contents($ocPath, false, null, 0, 5), '%PDF');

        return $esPdf
            ? $this->firmarPdf($ocPath, $firmaPng, $pagina, $xFrac, $yFrac, $wFrac)
            : $this->firmarImagen($ocPath, $firmaPng, $xFrac, $yFrac, $wFrac);
    }

    private function firmarPdf(string $ocPath, string $firmaPng, int $pagina, float $xFrac, float $yFrac, float $wFrac): string
    {
        // Tamaño de la primera página, para construir el mPDF con ese formato
        // (si no, mPDF arranca con una hoja A4 en blanco de más).
        $probe = new Mpdf(['tempDir' => $this->tmp]);
        $probe->setSourceFile($ocPath);
        $s1 = $probe->getTemplateSize($probe->importPage(1));
        unset($probe);

        $pdf = new Mpdf([
            'tempDir'       => $this->tmp,
            'format'        => [$s1['width'], $s1['height']],
            'orientation'   => $s1['orientation'] ?? 'P',
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
        ]);

        $n = $pdf->setSourceFile($ocPath);
        $pagina = max(1, min($pagina, $n));

        for ($i = 1; $i <= $n; $i++) {
            $tpl = $pdf->importPage($i);
            $sz  = $pdf->getTemplateSize($tpl);

            if ($i > 1) {
                $pdf->AddPageByArray([
                    'orientation' => $sz['orientation'] ?? 'P',
                    'sheet-size'  => [$sz['width'], $sz['height']],
                ]);
            }

            $pdf->useTemplate($tpl, ['x' => 0, 'y' => 0, 'width' => $sz['width'], 'height' => $sz['height']]);

            if ($i === $pagina) {
                $x = $xFrac * $sz['width'];
                $y = $yFrac * $sz['height'];
                $w = $wFrac * $sz['width'];
                // alto 0 = proporcional al ancho según el PNG.
                $pdf->Image($firmaPng, $x, $y, $w, 0, 'png', '', true, false);
            }
        }

        return $pdf->Output('', Destination::STRING_RETURN);
    }

    private function firmarImagen(string $ocImg, string $firmaPng, float $xFrac, float $yFrac, float $wFrac): string
    {
        // Se compone la firma SOBRE la imagen con GD (respeta la transparencia
        // del PNG) y la imagen plana se envuelve en un PDF por HTML. Así se evita
        // el Image() manual de mPDF, que falla si la página no está abierta.
        [$ocW, $ocH, $tipo] = getimagesize($ocImg) ?: [1240, 1754, IMAGETYPE_JPEG];

        $oc = match ($tipo) {
            IMAGETYPE_PNG  => imagecreatefrompng($ocImg),
            IMAGETYPE_GIF  => imagecreatefromgif($ocImg),
            IMAGETYPE_WEBP => imagecreatefromwebp($ocImg),
            default        => imagecreatefromjpeg($ocImg),
        };

        $sig  = imagecreatefrompng($firmaPng);
        $sigW0 = imagesx($sig);
        $sigH0 = imagesy($sig);

        $sigW = max(1, (int) round($wFrac * $ocW));
        $sigH = max(1, (int) round($sigW * $sigH0 / max(1, $sigW0)));
        $dx   = (int) round($xFrac * $ocW);
        $dy   = (int) round($yFrac * $ocH);

        imagealphablending($oc, true);
        imagecopyresampled($oc, $sig, $dx, $dy, 0, 0, $sigW, $sigH, $sigW0, $sigH0);

        $flat = $this->tmp . '/oc_flat_' . uniqid() . '.jpg';
        imagejpeg($oc, $flat, 92);
        imagedestroy($oc);
        imagedestroy($sig);

        $aspecto = $ocW / max(1, $ocH);
        if ($aspecto >= 1) { $pageW = 297; $pageH = round($pageW / $aspecto, 2); $orient = 'L'; }
        else               { $pageW = 210; $pageH = round($pageW / $aspecto, 2); $orient = 'P'; }

        $pdf = new Mpdf([
            'tempDir'       => $this->tmp,
            'format'        => [$pageW, $pageH],
            'orientation'   => $orient,
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
        ]);
        $pdf->WriteHTML('<img src="' . $flat . '" style="width:100%;height:auto">');

        $out = $pdf->Output('', Destination::STRING_RETURN);
        @unlink($flat);

        return $out;
    }
}
