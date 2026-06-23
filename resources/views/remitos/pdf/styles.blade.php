{{-- CSS compatible con mPDF (sin flexbox, sin var(), sin position:absolute) --}}
<style>
    body { font-family: dejavusans, sans-serif; color: #1a1a1a; font-size: 10px; line-height: 1.4; }

    .b { font-weight: bold; }
    .muted { color: #555; }
    .r { text-align: right; }
    .c { text-align: center; }
    .l { text-align: left; }

    /* ── Encabezado ── */
    .hd-strip { text-align: center; font-size: 10px; font-weight: bold; letter-spacing: 2px; padding-bottom: 2px; }

    table.hd { width: 100%; border-collapse: collapse; }
    table.hd td { border: 0.4mm solid #111; vertical-align: top; padding: 8px 10px; }
    td.hd-left  { width: 50%; }
    td.hd-letra { width: 14mm; border-left: none; border-right: none; text-align: center; vertical-align: middle; }
    td.hd-right { width: 50%; }

    .emp-name { font-size: 16.5px; font-weight: bold; margin-bottom: 4px; }
    .emp-row  { font-size: 10px; line-height: 1.6; }
    .emp-logo { width: 48px; height: 48px; }

    .letra-big { font-size: 30px; font-weight: bold; line-height: 1; }
    .letra-cod { font-size: 7px; font-weight: bold; }

    .doc-title { font-size: 17px; font-weight: bold; letter-spacing: 1px; }
    .doc-num   { font-size: 13.5px; font-weight: bold; margin-top: 3px; }
    .doc-row   { font-size: 10.5px; margin-top: 2px; }

    /* Destinatario */
    table.cli { width: 100%; border-collapse: collapse; border: 0.4mm solid #111; border-top: none; }
    table.cli td { padding: 5px 10px; font-size: 10.5px; vertical-align: top; }
    .chip { border: 0.3mm solid #9a9a9a; padding: 1px 5px; font-size: 9.5px; }

    /* ── Tabla de ítems (sin precios) ── */
    table.items { width: 100%; border-collapse: collapse; }
    table.items thead th {
        font-size: 9.5px; font-weight: bold;
        background-color: #ececec;
        border-top: 0.4mm solid #111; border-bottom: 0.4mm solid #111;
        padding: 6px 7px;
    }
    table.items td {
        font-size: 10px; padding: 6px 7px; vertical-align: top;
        border-bottom: 0.2mm solid #cfcfcf;
    }

    /* ── Cierre (solo última hoja): observaciones + firma ── */
    .obs-box { padding: 0; margin-top: 12px; font-size: 10px; }
    .obs-t   { font-weight: bold; margin-bottom: 3px; font-size: 10.5px; }
    .obs-meta{ color: #555; font-size: 9px; margin-top: 5px; }

    .firma { margin-top: 26px; text-align: center; width: 70mm; }
    .firma-line { border-top: 0.3mm solid #555; }
    .firma-lbl  { font-weight: bold; font-size: 10px; margin-top: 3px; }
    .firma-sub  { color: #777; font-size: 8.5px; }

    /* ── Pie (repetido): código fiscal del remito + N° de hoja ── */
    .cai-block { border: 0.3mm solid #9a9a9a; padding: 5px 8px; margin-bottom: 4px; }
    .cai-label { font-size: 8px; letter-spacing: 1px; text-transform: uppercase; color: #555; font-weight: bold; }
    .cai-code  { font-size: 12.5px; font-weight: bold; letter-spacing: .5px; }
    .cai-row   { font-size: 9px; margin-top: 1px; }

    table.pie { width: 100%; border-collapse: collapse; border-top: 0.5mm solid #111; }
    table.pie td { padding: 5px 5px 0; font-size: 9px; vertical-align: top; }
    .pie-pag { text-align: right; }
</style>
