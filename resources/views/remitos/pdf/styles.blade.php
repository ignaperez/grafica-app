{{-- CSS compatible con mPDF (sin flexbox, sin var(), sin position:absolute) --}}
<style>
    body { font-family: dejavusans, sans-serif; color: #1a1a1a; font-size: 8.5px; line-height: 1.35; }

    .b { font-weight: bold; }
    .muted { color: #555; }
    .r { text-align: right; }
    .c { text-align: center; }
    .l { text-align: left; }

    /* ── Encabezado ── */
    .hd-strip { text-align: center; font-size: 9px; font-weight: bold; letter-spacing: 2px; padding-bottom: 2px; }

    table.hd { width: 100%; border-collapse: collapse; }
    table.hd td { border: 0.4mm solid #111; vertical-align: top; padding: 6px 8px; }
    td.hd-left  { width: 50%; }
    td.hd-letra { width: 14mm; border-left: none; border-right: none; text-align: center; vertical-align: middle; }
    td.hd-right { width: 50%; }

    .emp-name { font-size: 14.5px; font-weight: bold; margin-bottom: 3px; }
    .emp-row  { font-size: 8.5px; line-height: 1.5; }
    .emp-logo { width: 42px; height: 42px; }

    .letra-big { font-size: 26px; font-weight: bold; line-height: 1; }
    .letra-cod { font-size: 7.5px; font-weight: bold; }

    .doc-title { font-size: 14.5px; font-weight: bold; letter-spacing: 1px; }
    .doc-num   { font-size: 12px; font-weight: bold; margin-top: 3px; }
    .doc-row   { font-size: 9px; margin-top: 2px; }

    /* Destinatario */
    table.cli { width: 100%; border-collapse: collapse; border: 0.4mm solid #111; border-top: none; }
    table.cli td { padding: 4px 8px; font-size: 9px; vertical-align: top; }
    .chip { border: 0.3mm solid #9a9a9a; padding: 1px 5px; font-size: 8.5px; }

    /* ── Tabla de ítems (sin precios) ── */
    table.items { width: 100%; border-collapse: collapse; }
    table.items thead th {
        font-size: 7.5px; font-weight: bold;
        background-color: #ececec;
        border-top: 0.4mm solid #111; border-bottom: 0.4mm solid #111;
        padding: 5px 6px;
    }
    table.items td {
        font-size: 8px; padding: 5px 6px; vertical-align: top;
        border-bottom: 0.2mm solid #cfcfcf;
    }

    /* ── Cierre (solo última hoja): observaciones + firma ── */
    .obs-box { border: 0.3mm solid #9a9a9a; padding: 6px 9px; margin-top: 12px; font-size: 8px; }
    .obs-t   { font-weight: bold; margin-bottom: 3px; }
    .obs-meta{ color: #555; font-size: 7.5px; margin-top: 5px; }

    .firma { margin-top: 26px; text-align: center; width: 70mm; }
    .firma-line { border-top: 0.3mm solid #555; }
    .firma-lbl  { font-weight: bold; font-size: 8px; margin-top: 3px; }
    .firma-sub  { color: #777; font-size: 7px; }

    /* ── Pie (repetido): código fiscal del remito + N° de hoja ── */
    .cai-block { border: 0.3mm solid #9a9a9a; padding: 5px 8px; margin-bottom: 4px; }
    .cai-label { font-size: 7px; letter-spacing: 1px; text-transform: uppercase; color: #555; font-weight: bold; }
    .cai-code  { font-size: 11px; font-weight: bold; letter-spacing: .5px; }
    .cai-row   { font-size: 7.5px; margin-top: 1px; }

    table.pie { width: 100%; border-collapse: collapse; border-top: 0.5mm solid #111; }
    table.pie td { padding: 4px 4px 0; font-size: 7.5px; vertical-align: top; }
    .pie-pag { text-align: right; }
</style>
