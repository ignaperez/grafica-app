{{-- CSS compatible con mPDF (sin flexbox, sin var(), sin position:absolute) --}}
<style>
    body { font-family: dejavusans, sans-serif; color: #1a1a1a; font-size: 8.5px; line-height: 1.35; }

    .b   { font-weight: bold; }
    .muted { color: #555; }
    .r   { text-align: right; }
    .c   { text-align: center; }
    .l   { text-align: left; }

    /* ── Encabezado ───────────────────────────────────────────── */
    .hd-strip { text-align: center; font-size: 8px; font-weight: bold; letter-spacing: 2px; padding-bottom: 2px; }

    table.hd { width: 100%; border-collapse: collapse; }
    table.hd td { border: 0.4mm solid #111; vertical-align: top; padding: 8px 10px; }
    td.hd-left  { width: 50%; }
    td.hd-letra { width: 14mm; border-left: none; border-right: none; text-align: center; vertical-align: middle; }
    td.hd-right { width: 50%; }

    .emp-name { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
    .emp-row  { font-size: 8px; line-height: 1.6; }
    .emp-logo { width: 48px; height: 48px; }

    .letra-big { font-size: 30px; font-weight: bold; line-height: 1; }
    .letra-cod { font-size: 7px; font-weight: bold; }

    .doc-title { font-size: 15px; font-weight: bold; letter-spacing: 1px; margin-bottom: 4px; }
    .doc-row   { font-size: 8.5px; line-height: 1.7; }
    .doc-sep   { border-top: 0.2mm solid #bbb; margin: 4px 0; }

    /* Datos del cliente */
    table.cli { width: 100%; border-collapse: collapse; border: 0.4mm solid #111; border-top: none; }
    table.cli td { padding: 5px 10px; font-size: 8.5px; vertical-align: top; }

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

    /* ── Transparencia fiscal ─────────────────────────────────── */
    .transp { border: 0.3mm solid #9a9a9a; padding: 5px 9px; margin-top: 8px; font-size: 8px; }
    .transp-t { font-weight: bold; margin-bottom: 2px; }

    /* ── Banda de cierre: Observaciones + Total (última hoja) ──── */
    table.cierre { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.cierre td { border: 0.4mm solid #111; vertical-align: top; padding: 7px 10px; }
    td.cierre-obs { width: 58%; font-size: 8px; }
    td.cierre-tot { width: 42%; }
    .obs-t { font-weight: bold; margin-bottom: 3px; font-size: 8px; }

    td.cierre-tot td { border: none; padding: 1px 0; font-size: 9px; }
    td.cierre-tot td.lbl { text-align: right; padding-right: 8px; }
    td.cierre-tot td.val { text-align: right; font-weight: bold; }
    .tot-final td { font-size: 14px; font-weight: bold; border-top: 0.4mm solid #111 !important; padding-top: 5px !important; }
    .tot-words { text-align: right; font-size: 8px; margin-top: 5px; }

    /* ── Pie (repetido): QR + CAE + N° de hoja, SIN código de barras ── */
    table.pie { width: 100%; border-collapse: collapse; border-top: 0.5mm solid #111; }
    table.pie td { vertical-align: top; padding: 7px 8px 0; font-size: 8px; }
    td.pie-qr  { width: 30mm; }
    td.pie-cae { width: auto; line-height: 1.6; }
    .cae-auth  { font-weight: bold; font-size: 9px; margin-bottom: 3px; }
    td.pie-emp { width: 48mm; text-align: right; font-size: 8px; line-height: 1.5; }
    .pie-pag   { color: #555; margin-top: 4px; }
</style>
