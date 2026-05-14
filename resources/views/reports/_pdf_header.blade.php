{{-- resources/views/reports/_pdf_header.blade.php --}}
{{-- DomPDF-safe: table layout only, no flex/grid --}}
<style>
  * { margin:0; padding:0; box-sizing:border-box; }

  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    color: #222;
    background: #fff;
    padding: 20px 24px 24px;
  }

  /* ── Header 3-column: dealer | logo | report info ── */
  .hdr-table { width:100%; border-collapse:collapse; margin-bottom:4px; }
  .hdr-left  { vertical-align:middle; width:34%; }
  .hdr-mid   { vertical-align:middle; text-align:center; width:32%; }
  .hdr-right { vertical-align:middle; text-align:right; width:34%; }

  .dealer-name  { font-size:12px; font-weight:bold; color:#333; }
  .dealer-sub   { font-size:9px; color:#777; margin-top:2px; }

  .report-title-block { font-size:9px; color:#555; text-transform:uppercase; letter-spacing:0.4px; }
  .generated-by       { font-size:8px; color:#888; margin-top:3px; }

  /* ── Green rule ─────────────────────────────────── */
  .hdr-rule { border:none; border-top:3px solid #B1D83C; margin:6px 0 6px; }

  /* ── Meta row ────────────────────────────────────── */
  .meta-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
  .meta-left  { font-size:9px; color:#555; vertical-align:middle; }
  .meta-right { font-size:9px; color:#888; text-align:right; vertical-align:middle; }

  /* ── Section title ───────────────────────────────── */
  .sec-title {
    background: #B1D83C;
    color: #222;
    font-size: 10px;
    font-weight: bold;
    padding: 5px 8px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* ── Tables ──────────────────────────────────────── */
  table.rpt { width:100%; border-collapse:collapse; margin-bottom:12px; }

  table.rpt thead tr { background:#333; color:#fff; }
  table.rpt thead th {
    padding: 6px 8px;
    font-size: 9px;
    font-weight: bold;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  table.rpt thead th.r { text-align:right; }
  table.rpt thead th.c { text-align:center; }

  table.rpt tbody tr   { background:#fff; }
  table.rpt tbody tr.alt { background:#f7f9f0; }

  table.rpt tbody td {
    padding: 5px 8px;
    font-size: 10px;
    color: #333;
    border-bottom: 1px solid #e8e8e8;
    vertical-align: top;
  }
  table.rpt tbody td.r { text-align:right; }
  table.rpt tbody td.c { text-align:center; }

  /* ── Pack size shown below product name ──────────── */
  .prod-pack {
    display: block;
    font-size: 8.5px;
    color: #888;
    font-style: italic;
    margin-top: 1px;
  }

  /* ── Product group header row ────────────────────── */
  tr.prod-hdr td {
    background: #B1D83C;
    color: #222;
    font-weight: bold;
    font-size: 10px;
    padding: 6px 8px;
  }
  tr.prod-hdr td.r { text-align:right; }

  /* ── Sub-header inside product group ─────────────── */
  tr.sub-hdr th {
    background: #f0f5d8;
    color: #444;
    font-size: 9px;
    font-weight: bold;
    padding: 4px 8px;
    text-align: left;
    text-transform: uppercase;
    border-bottom: 1px solid #c8d870;
  }
  tr.sub-hdr th.r { text-align:right; }
  tr.sub-hdr th.c { text-align:center; }

  /* ── Totals row ──────────────────────────────────── */
  tr.tot td {
    background: #333;
    color: #fff;
    font-weight: bold;
    font-size: 10px;
    padding: 6px 8px;
    border: none;
  }
  tr.tot td.r { text-align:right; color:#B1D83C; }

  /* ── Age badge ───────────────────────────────────── */
  .age {
    font-size: 8px;
    font-weight: bold;
    padding: 1px 5px;
    border-radius: 3px;
    color: #fff;
    display: inline-block;
  }
  .age-fresh { background:#5a9e3f; }
  .age-mid   { background:#d4820a; }
  .age-old   { background:#c0392b; }

  /* ── Date bar ────────────────────────────────────── */
  .date-bar {
    background: #444;
    color: #fff;
    font-size: 10px;
    font-weight: bold;
    padding: 5px 8px;
    margin-bottom: 0;
  }

  /* ── Grand total bar ─────────────────────────────── */
  .grand-bar { width:100%; border-collapse:collapse; margin-top:4px; }
  .grand-bar td {
    background: #B1D83C;
    color: #222;
    font-weight: bold;
    font-size: 11px;
    padding: 7px 10px;
  }
  .grand-bar td.r { text-align:right; }

  /* ── Footer ──────────────────────────────────────── */
  .footer-table { width:100%; border-collapse:collapse; margin-top:18px; border-top:1px solid #ddd; }
  .footer-table td { font-size:8px; color:#aaa; padding-top:5px; }
  .footer-table td.r { text-align:right; }
</style>

{{-- ═══ HEADER ═══ --}}
<table class="hdr-table">
  <tr>
    {{-- LEFT: Dealer info --}}
    <td class="hdr-left">
      @if(!empty($data['dealer']))
        <div class="dealer-name">
          {{ $data['dealer']['business_name'] ?? $data['dealer']['name'] ?? '' }}
        </div>
        @if(!empty($data['dealer']['city']))
          <div class="dealer-sub">{{ $data['dealer']['city'] }}</div>
        @endif
      @endif
    </td>

    {{-- CENTRE: Logo --}}
    <td class="hdr-mid">
      <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg"
           alt="Greenwave"
           style="width:130px; height:auto;" />
    </td>

    {{-- RIGHT: Report title + generated by --}}
    <td class="hdr-right">
      <div class="report-title-block">{{ $title ?? '' }}</div>
      @if(!empty($data['dealer']))
        <div class="generated-by">
          Generated by:
          <strong>{{ $data['dealer']['business_name'] ?? $data['dealer']['name'] ?? '' }}</strong>
        </div>
      @endif
      <div class="generated-by">
        {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}
      </div>
    </td>
  </tr>
</table>

<hr class="hdr-rule">

<table class="meta-table">
  <tr>
    <td class="meta-left">
      @if(!empty($data['ctx']->dateFrom) || !empty($data['ctx']->dateTo))
        Period:
        <strong>
          {{ !empty($data['ctx']->dateFrom) ? \Carbon\Carbon::parse($data['ctx']->dateFrom)->format('d M Y') : '—' }}
          to
          {{ !empty($data['ctx']->dateTo) ? \Carbon\Carbon::parse($data['ctx']->dateTo)->format('d M Y') : '—' }}
        </strong>
      @else
        All dates (no date filter applied)
      @endif
    </td>
    <td class="meta-right">Greenwave — Confidential</td>
  </tr>
</table>

<div class="sec-title">{{ $subtitle ?? $title ?? '' }}</div>
