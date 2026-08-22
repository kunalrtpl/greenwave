{{-- resources/views/reports/_pdf_style.blade.php --}}
{{-- CSS only — safe to @include inside <head>. mPDF pulls <style> out of <head> but
     silently drops any other markup found there, so actual header content must live
     in _pdf_header.blade.php and be @include'd from <body> instead. --}}
<style>
  * { margin:0; padding:0; box-sizing:border-box; }

  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    color: #27272a;
    background: #fff;
    padding: 20px 24px 24px;
  }

  /* ── Header 3-column: dealer | logo | report info ── */
  .hdr-table { width:100%; border-collapse:collapse; margin-bottom:0; }
  .hdr-left  { vertical-align:middle; width:34%; }
  .hdr-mid   { vertical-align:middle; text-align:center; width:32%; }
  .hdr-right { vertical-align:middle; text-align:right; width:34%; }

  .dealer-name  { font-size:13px; font-weight:bold; color:#1e293b; }
  .dealer-sub   { font-size:8.5px; color:#94a3b8; margin-top:3px; text-transform:uppercase; letter-spacing:0.6px; }

  .logo-img       { width:130px; height:auto; }
  .logo-fallback  { font-size:19px; font-weight:bold; color:#B1D83C; letter-spacing:1.5px; }

  .report-title-block {
    font-size:11px; font-weight:bold; color:#1e293b;
    text-transform:uppercase; letter-spacing:0.8px;
  }
  .generated-by { font-size:8px; color:#94a3b8; margin-top:4px; }
  .generated-by strong { color:#475569; }

  /* ── Two-tone accent rule (brand lime + hairline) ── */
  .hdr-rule-1 { border-top:3px solid #B1D83C; margin-top:10px; }
  .hdr-rule-2 { border-top:1px solid #e4e4e7; margin-top:3px; margin-bottom:14px; }

  /* ── Meta chip row (period / generated info) + confidential badge ── */
  .meta-row { width:100%; border-collapse:collapse; margin-bottom:16px; }
  .meta-left  { vertical-align:middle; }
  .meta-right { vertical-align:middle; text-align:right; }

  .meta-chip {
    display:inline-block; font-size:8.5px; color:#334155;
    border:1px solid #e4e4e7; background:#fafafa;
    border-radius:3px; padding:4px 9px; margin-right:6px;
  }
  .meta-chip-lbl {
    color:#94a3b8; font-weight:bold; text-transform:uppercase;
    letter-spacing:0.5px; margin-right:4px;
  }
  .meta-chip-val { color:#0f172a; font-weight:bold; }

  .confidential-badge {
    display:inline-block; font-size:7.5px; font-weight:bold;
    color:#64748b; background:#f4f4f5; border:1px solid #e4e4e7;
    border-radius:3px; padding:4px 9px; text-transform:uppercase; letter-spacing:0.6px;
  }

  /* ── Section title bar ── */
  .sec-title {
    background:#eef1f5; color:#1e293b;
    font-size:10.5px; font-weight:bold;
    padding:8px 12px; margin-bottom:10px;
    text-transform:uppercase; letter-spacing:1px;
    border-left:4px solid #B1D83C;
    border-top:1px solid #dde3ea; border-right:1px solid #dde3ea; border-bottom:1px solid #dde3ea;
  }
  /* ── Tables ── */
  table.rpt { width:100%; border-collapse:collapse; margin-bottom:12px; border:1px solid #d4d4d8; }

  table.rpt thead tr { background:#eef1f5; }
  table.rpt thead th {
    padding: 7px 9px;
    font-size: 8.5px;
    font-weight: bold;
    color: #334155;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border-right: 1px solid #dde3ea;
    border-bottom: 1px solid #d4d4d8;
  }
  table.rpt thead th:last-child { border-right: none; }
  table.rpt thead th.r { text-align:right; }
  table.rpt thead th.c { text-align:center; }

  table.rpt tbody tr   { background:#fff; }
  table.rpt tbody tr.alt { background:#fafbf5; }

  table.rpt tbody td {
    padding: 6px 9px;
    font-size: 9.5px;
    color: #334155;
    border-bottom: 1px solid #e4e4e7;
    border-right: 1px solid #f1f1f2;
    vertical-align: top;
  }
  table.rpt tbody td:last-child { border-right: none; }
  table.rpt tbody td.r { text-align:right; }
  table.rpt tbody td.c { text-align:center; }

  /* ── Pack size shown below product name ── */
  .prod-pack {
    display: block;
    font-size: 8.5px;
    color: #94a3b8;
    font-style: italic;
    margin-top: 1px;
  }

  /* ── Product / actor group header row ── */
  tr.prod-hdr td {
    background: #B1D83C;
    color: #1a2e05;
    font-weight: bold;
    font-size: 10px;
    padding: 7px 9px;
    border-right: 1px solid #9ab82e;
  }
  tr.prod-hdr td:last-child { border-right: none; }
  tr.prod-hdr td.r { text-align:right; }

  /* ── Sub-header inside product group ── */
  tr.sub-hdr th {
    background: #f0f5d8;
    color: #3f4e1c;
    font-size: 8.5px;
    font-weight: bold;
    padding: 5px 9px;
    text-align: left;
    text-transform: uppercase;
    letter-spacing:0.3px;
    border-bottom: 1px solid #c8d870;
    border-right: 1px solid #dde8a0;
  }
  tr.sub-hdr th:last-child { border-right: none; }
  tr.sub-hdr th.r { text-align:right; }
  tr.sub-hdr th.c { text-align:center; }

  /* ── Totals row ── */
  tr.tot td {
    background: #e2e8f0;
    color: #1e293b;
    font-weight: bold;
    font-size: 9.5px;
    padding: 7px 9px;
    border-right: 1px solid #cbd5e1;
  }
  tr.tot td:last-child { border-right: none; }
  tr.tot td.r { text-align:right; color:#1e293b; }

  /* ── Age badge ── */
  .age {
    font-size: 8px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 3px;
    color: #fff;
    display: inline-block;
  }
  .age-fresh { background:#5a9e3f; }
  .age-mid   { background:#d4820a; }
  .age-old   { background:#c0392b; }

  /* ── Date bar ── */
  .date-bar {
    background: #eef1f5;
    color: #1e293b;
    font-size: 9.5px;
    font-weight: bold;
    letter-spacing: 0.4px;
    padding: 6px 9px;
    margin-bottom: 0;
    border-left: 3px solid #B1D83C;
    border-top:1px solid #dde3ea; border-right:1px solid #dde3ea;
  }

  /* ── In-transit style callout total (kept for views that build inline) ── */
  .callout-total {
    width:100%; border-collapse:collapse; margin-top:2px;
  }
  .callout-total td.spacer { width:80%; }
  .callout-total td.box {
    width:20%; background:#eef1f5; border:1px solid #dde3ea;
    padding:8px 11px; text-align:right; border-left:3px solid #B1D83C;
  }
  {{-- NOTE: kept as standalone (non-descendant) class selectors on purpose —
       mPDF silently drops color/font rules reached via a ".parent .child"
       descendant selector when the child sits inside a <td>. --}}
  .ct-lbl {
    font-size:8px; color:#5a7a1c; text-transform:uppercase; letter-spacing:0.5px;
  }
  .ct-val { font-size:13px; color:#1e293b; font-weight:bold; }

  /* ── Report summary block (totals stacked right) ── */
  .summary-table { width:100%; border-collapse:collapse; margin-top:10px; }
  .summary-spacer { width:60%; border-top:2px solid #B1D83C; }
  .summary-box {
    width: 40%;
    border-top: 2px solid #B1D83C;
    padding-top: 9px;
    text-align: right;
    vertical-align: top;
  }
  .summary-label {
    font-size: 8.5px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
  }
  .summary-qty {
    font-size: 13px;
    font-weight: bold;
    color: #475569;
    display: block;
    margin-bottom: 7px;
  }
  .summary-value {
    font-size: 16px;
    font-weight: bold;
    color: #1e293b;
    display: block;
  }
  .summary-divider {
    border: none;
    border-top: 1px solid #e4e4e7;
    margin: 5px 0 7px;
  }

  /* ── Footer note (report-specific caption; page numbers come from the
       repeating mPDF footer set in the controller) ── */
  .footer-table { width:100%; border-collapse:collapse; margin-top:18px; border-top:1px solid #e4e4e7; }
  .footer-table td { font-size:7.5px; color:#a1a1aa; padding-top:6px; font-style:italic; }
</style>
