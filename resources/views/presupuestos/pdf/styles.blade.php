{{-- CSS compatible con mPDF: sin flexbox, sin var(), sin position:absolute,
     sin grid. Todo el layout va con tablas. Replica la estética de
     presupuestos/print.blade.php (acento #e6502a, texto #15171a). --}}
<style>
    body { font-family: dejavusans, sans-serif; color: #15171a; font-size: 10px; line-height: 1.45; }

    .b      { font-weight: bold; }
    .muted  { color: #7a8189; }
    .soft   { color: #5b636d; }
    .r      { text-align: right; }
    .c      { text-align: center; }
    .l      { text-align: left; }
    .mono   { font-family: dejavusansmono, monospace; }

    .label {
        font-size: 7.5px; letter-spacing: 1.4px; text-transform: uppercase;
        color: #7a8189; font-weight: bold;
    }

    /* ── Encabezado (repetido en cada hoja) ── */
    table.hd { width: 100%; border-collapse: collapse; }
    table.hd td { border: none; vertical-align: top; padding: 0; }

    .emp-name { font-size: 15px; font-weight: bold; }
    .emp-meta { font-size: 8.5px; color: #5b636d; line-height: 1.55; margin-top: 2px; }

    .doc-num {
        font-family: dejavusansmono, monospace;
        font-size: 21px; font-weight: bold; color: #15171a;
    }
    .doc-dot { color: #e6502a; font-size: 13px; }
    table.doc-dl { border-collapse: collapse; margin-top: 4px; }
    table.doc-dl td { border: none; padding: 1px 0 1px 10px; font-size: 8.5px; }
    table.doc-dl td.dt { color: #7a8189; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
    table.doc-dl td.dd { font-family: dejavusansmono, monospace; }

    .hd-rule { border-bottom: 0.3mm solid #e8eaed; }

    /* ── Cliente (solo primera hoja: va en el cuerpo) ── */
    .who-name { font-size: 13.5px; font-weight: bold; margin: 3px 0 2px; }
    .who-row  { font-size: 9.5px; color: #41464d; line-height: 1.5; }

    /* ── Tabla de ítems ── */
    table.items { width: 100%; border-collapse: collapse; margin-top: 14px; }
    table.items thead th {
        text-align: left; font-size: 7.5px; text-transform: uppercase;
        letter-spacing: 1.2px; font-weight: bold; color: #15171a;
        padding: 7px 6px; border-bottom: 0.5mm solid #15171a;
    }
    table.items thead th.r { text-align: right; }
    table.items thead th.c { text-align: center; }

    table.items td {
        padding: 7px 6px; vertical-align: top; font-size: 9.5px;
        border-bottom: 0.2mm solid #f0f1f3;
    }
    table.items td.idx  { color: #9aa0a8; font-family: dejavusansmono, monospace; font-size: 8.5px; }
    table.items td.u    { text-align: center; color: #5b636d; }
    table.items td.num  { text-align: right; font-family: dejavusansmono, monospace; }
    table.items td.s    { font-weight: bold; }
    .desc-main { font-weight: bold; }
    .desc-sub  { color: #7a8189; font-size: 8.5px; margin-top: 1px; }

    /* ── Cierre: total + condiciones (solo última hoja) ── */
    table.tot { border-collapse: collapse; margin-left: auto; }
    table.tot td { border: none; padding: 0; }
    td.tot-bar { width: 1.3mm; background-color: #e6502a; }
    td.tot-box { background-color: #15171a; color: #ffffff; padding: 9px 14px; }
    .tot-lbl { font-size: 8px; letter-spacing: 1.6px; text-transform: uppercase; color: #b8bcc2; }
    .tot-val { font-family: dejavusansmono, monospace; font-size: 17px; font-weight: bold; color: #ffffff; }

    .cond {
        border: 0.3mm solid #e8eaed; padding: 9px 12px; margin-top: 12px;
    }
    .cond-t { font-size: 7.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #7a8189; font-weight: bold; }
    .cond-p { font-size: 9px; line-height: 1.5; color: #2c3036; margin-top: 4px; }
    .cond-d { font-size: 8px; color: #7a8189; margin-top: 7px; padding-top: 5px; border-top: 0.2mm solid #e8eaed; }

    /* ── Pie (repetido en cada hoja) ── */
    table.pie { width: 100%; border-collapse: collapse; border-top: 0.3mm solid #e8eaed; }
    table.pie td { border: none; padding: 5px 0 0; font-size: 8px; color: #7a8189; vertical-align: top; }
    .pie-pag { text-align: right; font-family: dejavusansmono, monospace; }
</style>
