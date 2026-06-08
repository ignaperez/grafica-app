{{-- CSS compatible con mPDF (sin flexbox, sin var(), sin position:absolute) --}}
<style>
    body { font-family: dejavusans, sans-serif; color: #1a1a1a; font-size: 8.5px; line-height: 1.35; }

    .b   { font-weight: bold; }
    .muted { color: #555; }
    .r   { text-align: right; }
    .c   { text-align: center; }
    .l   { text-align: left; }
    .nowrap { white-space: nowrap; }

    /* ── Encabezado ───────────────────────────────────────────── */
    .hd-strip { text-align: center; font-size: 8px; font-weight: bold; letter-spacing: 2px; padding-bottom: 2px; }

    table.hd { width: 100%; border-collapse: collapse; }
    table.hd td { border: 0.4mm solid #111; vertical-align: top; padding: 6px 8px; }
    td.hd-left  { width: 50%; }
    td.hd-letra { width: 14mm; border-left: none; border-right: none; text-align: center; vertical-align: middle; }
    td.hd-right { width: 50%; }

    .emp-name { font-size: 13px; font-weight: bold; margin-bottom: 3px; }
    .emp-row  { font-size: 7.5px; line-height: 1.5; }
    .emp-logo { width: 42px; height: 42px; }

    .letra-big { font-size: 26px; font-weight: bold; line-height: 1; }
    .letra-cod { font-size: 6.5px; font-weight: bold; }

    .doc-title { font-size: 13px; font-weight: bold; letter-spacing: 1px; }
    .doc-num   { font-size: 11px; font-weight: bold; margin-top: 3px; }
    .doc-row   { font-size: 8px; margin-top: 2px; }

    /* Datos del cliente */
    table.cli { width: 100%; border-collapse: collapse; border: 0.4mm solid #111; border-top: none; margin-top: 0; }
    table.cli td { padding: 4px 8px; font-size: 8px; vertical-align: top; }

    /* ── Tabla de ítems ───────────────────────────────────────── */
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

    /* ── Totales (solo última hoja) ───────────────────────────── */
    .tot-wrap { margin-top: 8px; }
    table.tot { border-collapse: collapse; float: right; }
    table.tot td { padding: 2px 6px; font-size: 9px; }
    table.tot td.lbl { text-align: right; }
    table.tot td.val { text-align: right; font-weight: bold; min-width: 30mm; }
    .tot-final td { font-size: 13px; font-weight: bold; border-top: 0.4mm solid #111; padding-top: 5px; }

    .monto-letras { clear: both; padding-top: 6px; font-size: 8px; }
    .transp { border: 0.3mm solid #9a9a9a; padding: 5px 8px; margin-top: 8px; font-size: 8px; }
    .transp-t { font-weight: bold; margin-bottom: 3px; }
    .obs { margin-top: 8px; font-size: 8px; }

    /* ── Pie (repetido en cada hoja) ──────────────────────────── */
    table.pie { width: 100%; border-collapse: collapse; border-top: 0.5mm solid #111; }
    table.pie td { vertical-align: top; padding: 6px 6px 0; font-size: 7.5px; }
    td.pie-qr  { width: 24mm; }
    .pie-qr img { width: 22mm; height: 22mm; }
    td.pie-cae { width: auto; }
    .cae-auth  { font-weight: bold; font-size: 8px; margin-bottom: 2px; }
    td.pie-bc  { width: 52mm; text-align: center; }
    .bc-num    { font-size: 7px; letter-spacing: .5px; margin-top: 1px; }
    .pie-pag   { text-align: right; font-size: 7.5px; color: #555; padding-top: 3px; }
</style>
