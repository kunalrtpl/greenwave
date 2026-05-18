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
  table.rpt { width:100%; border-collapse:collapse; margin-bottom:12px; border:1px solid #ccc; }

  table.rpt thead tr { background:#2d2d2d; color:#fff; }
  table.rpt thead th {
    padding: 7px 9px;
    font-size: 9px;
    font-weight: bold;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-right: 1px solid #444;
  }
  table.rpt thead th:last-child { border-right: none; }
  table.rpt thead th.r { text-align:right; }
  table.rpt thead th.c { text-align:center; }

  table.rpt tbody tr   { background:#fff; }
  table.rpt tbody tr.alt { background:#f5f8ec; }

  table.rpt tbody td {
    padding: 6px 9px;
    font-size: 10px;
    color: #333;
    border-bottom: 1px solid #ddd;
    border-right: 1px solid #e5e5e5;
    vertical-align: top;
  }
  table.rpt tbody td:last-child { border-right: none; }
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
    color: #1a1a1a;
    font-weight: bold;
    font-size: 10px;
    padding: 7px 9px;
    border-right: 1px solid #9ab82e;
  }
  tr.prod-hdr td:last-child { border-right: none; }
  tr.prod-hdr td.r { text-align:right; }

  /* ── Sub-header inside product group ─────────────── */
  tr.sub-hdr th {
    background: #f0f5d8;
    color: #444;
    font-size: 9px;
    font-weight: bold;
    padding: 5px 9px;
    text-align: left;
    text-transform: uppercase;
    border-bottom: 1px solid #c8d870;
    border-right: 1px solid #dde8a0;
  }
  tr.sub-hdr th:last-child { border-right: none; }
  tr.sub-hdr th.r { text-align:right; }
  tr.sub-hdr th.c { text-align:center; }

  /* ── Totals row ──────────────────────────────────── */
  tr.tot td {
    background: #2d2d2d;
    color: #fff;
    font-weight: bold;
    font-size: 10px;
    padding: 7px 9px;
    border-right: 1px solid #444;
  }
  tr.tot td:last-child { border-right: none; }
  tr.tot td.r { text-align:right; color:#fff; }

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
  .grand-bar { width:100%; border-collapse:collapse; margin-top:4px; border:1px solid #ccc; }
  .grand-bar td {
    background: #2d2d2d;
    color: #fff;
    font-weight: bold;
    font-size: 11px;
    padding: 8px 10px;
    border-right: 1px solid #444;
  }
  .grand-bar td:last-child { border-right: none; }
  .grand-bar td.r { text-align:right; color:#B1D83C; }

  /* ── Report summary block (totals stacked right) ─── */
  .summary-table { width:100%; border-collapse:collapse; margin-top:8px; }
  .summary-spacer { width:60%; border-top:2px solid #B1D83C; }
  .summary-box {
    width: 40%;
    border-top: 2px solid #B1D83C;
    padding-top: 8px;
    text-align: right;
    vertical-align: top;
  }
  .summary-label {
    font-size: 9px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    display: block;
  }
  .summary-qty {
    font-size: 13px;
    font-weight: bold;
    color: #444;
    display: block;
    margin-bottom: 6px;
  }
  .summary-value {
    font-size: 15px;
    font-weight: bold;
    color: #222;
    display: block;
  }
  .summary-divider {
    border: none;
    border-top: 1px solid #ddd;
    margin: 4px 0 6px;
  }

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

    {{-- CENTRE: Logo (base64 embedded for DomPDF – no remote fetch needed) --}}
    <td class="hdr-mid">
      @php
        $logoPath  = public_path('images/greenwave-logo.jpg');
        $logoCache = public_path('images/greenwave-logo-b64.txt');
        // Download once and cache as base64 file
        if (!file_exists($logoCache)) {
            if (!file_exists(public_path('images'))) {
                mkdir(public_path('images'), 0755, true);
            }
            $raw = @file_get_contents('https://g2app.in/images/greenwave-logo-1-275-sl.jpg');
            if ($raw) {
                file_put_contents($logoPath, $raw);
                file_put_contents($logoCache, base64_encode($raw));
            }
        }
        $logoB64 = file_exists($logoCache) ? file_get_contents($logoCache) : null;
      @endphp
      @if($logoB64)
        <img src="data:image/jpeg;base64,{{ $logoB64 }}"
             alt="Greenwave"
             style="width:130px; height:auto;" />
      @else
        <span style="font-size:18px;font-weight:bold;color:#B1D83C;letter-spacing:1px;">GREENWAVE</span>
      @endif
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
        <!-- All dates (no date filter applied) -->
      @endif
    </td>
    <td class="meta-right">Greenwave — Confidential</td>
  </tr>
</table>

<div class="sec-title">{{ $subtitle ?? $title ?? '' }}</div>