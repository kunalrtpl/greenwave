<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #1e293b;
    background: #ffffff;
    line-height: 1.5;
}

/* ═══════════════════════════════════════
   HEADER
═══════════════════════════════════════ */
.hdr-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
}
.hdr-left  { vertical-align: middle; text-align: left; }
.hdr-right { vertical-align: middle; text-align: right; }

.logo-img { width: 150px; height: auto; }

.hdr-doc-type {
    font-size: 13px;
    font-weight: bold;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 4px;
}
.hdr-date { 
    font-size: 8px; 
    color: #64748b; 
    margin-top: 2px;
}

/* ═══════════════════════════════════════
   SECTION HEADER BAR
═══════════════════════════════════════ */
.section-bar {
    background: #1a7f3c;
    color: #ffffff;
    font-size: 8px;
    font-weight: bold;
    padding: 7px 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* ═══════════════════════════════════════
   DATA ROWS
═══════════════════════════════════════ */
.main-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
    /* This prevents mPDF from breaking the section across multiple pages */
    page-break-inside: avoid; 
}

.label-cell {
    width: 40%;
    padding: 8px 12px;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
    font-weight: bold;
    font-size: 8px;
    vertical-align: top;
    background: #f8fafc;
}

.value-cell {
    padding: 8px 12px;
    border-bottom: 1px solid #e2e8f0;
    color: #1e293b;
    font-size: 8.5px;
    vertical-align: top;
    background: #ffffff;
}

.firm-name {
    font-weight: bold;
    color: #0f172a;
}

/* ═══════════════════════════════════════
   CHIPS — each option its own box
═══════════════════════════════════════ */
.chip {
    display: inline-block;
    background: #e8f5e9;
    color: #1a7f3c;
    border: 1px solid #a5d6a7;
    border-radius: 3px;
    padding: 2px 8px;
    margin: 2px 4px 2px 0;
    font-size: 8px;
    font-weight: bold;
    white-space: nowrap;
}

.chip-lead {
    display: inline-block;
    background: #e8f5e9;
    color: #1a7f3c;
    border: 1px solid #a5d6a7;
    border-radius: 3px;
    padding: 2px 8px;
    font-size: 8px;
    font-weight: bold;
}

.not-provided {
    color: #94a3b8;
    font-style: italic;
    font-size: 8px;
}

/* ═══════════════════════════════════════
   FOOTER
═══════════════════════════════════════ */
.footer-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-top: 1px solid #cbd5e1;
    padding-top: 6px;
}
.footer-left  { font-size: 7.5px; font-weight: bold; color: #334155; }
.footer-mid   { font-size: 7px; color: #64748b; text-align: center; }
.footer-right { font-size: 7px; color: #64748b; text-align: right; }
</style>
</head>
<body>

{{-- ── HEADER ── --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Prospective Channel Partner Evaluation</div>
            {{-- Moved Submission Info Here --}}
            <div class="hdr-date"><strong>Submitted By:</strong> {{ $submittedBy ?? '—' }}</div>
            <div class="hdr-date"><strong>Submitted At:</strong> {{ $submittedAt }}</div>
        </td>
    </tr>
</table>

{{-- ── SECTION A — BASIC INFORMATION ── --}}
<table class="main-table" cellspacing="0" cellpadding="0">

    <tr>
        <td colspan="2" class="section-bar">Section A &ndash; Basic Information</td>
    </tr>

    <tr>
        <td class="label-cell">Firm Name</td>
        <td class="value-cell firm-name">{{ $dealer->business_name }}</td>
    </tr>
    <tr>
        <td class="label-cell">Contact Person</td>
        <td class="value-cell">{{ $dealer->name ?? '—' }}</td>
    </tr>
    @if(!empty($dealer->designation))
    <tr>
        <td class="label-cell">Designation</td>
        <td class="value-cell">{{ $dealer->designation }}</td>
    </tr>
    @endif
    <tr>
        <td class="label-cell">Mobile Number</td>
        <td class="value-cell">{{ $dealer->owner_mobile }}</td>
    </tr>
    <tr>
        <td class="label-cell">Email</td>
        <td class="value-cell">{{ $dealer->email ?: '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">City</td>
        <td class="value-cell">{{ $dealer->city }}</td>
    </tr>
    @if(!empty($territory))
    <tr>
        <td class="label-cell">Territory Covered</td>
        <td class="value-cell">{{ $territory }}</td>
    </tr>
    @endif
    <tr>
        <td class="label-cell">Source of Lead</td>
        <td class="value-cell">
            <span class="chip-lead">{{ $dealer->source_of_lead ?: '—' }}</span>
        </td>
    </tr>
    {{-- Removed Submitted By and Submitted At from here --}}
</table>

{{-- ── SECTIONS B–E ── --}}
@php
    $grouped = $answers->groupBy('section_key');
@endphp

@foreach($grouped as $sectionKey => $sectionAnswers)
<table class="main-table" cellspacing="0" cellpadding="0">

    <tr>
        <td colspan="2" class="section-bar">
            Section {{ $sectionKey }} &ndash; {{ $sectionAnswers->first()->section_name }}
        </td>
    </tr>

    @foreach($sectionAnswers as $ans)
    @php
        $selected      = is_array($ans->selected_options) ? $ans->selected_options : json_decode($ans->selected_options, true) ?? [];
        $questionLabel = rtrim(trim($ans->question_text), ' *');
    @endphp
    <tr>
        <td class="label-cell">{{ $questionLabel }}</td>
        <td class="value-cell">
            @if(!empty($selected))
                @foreach($selected as $opt)
                    <span class="chip">{{ $opt }}</span>
                @endforeach
            @elseif(!empty($ans->custom_answer))
                {{ $ans->custom_answer }}
            @else
                <span class="not-provided">Not provided</span>
            @endif
        </td>
    </tr>
    @endforeach

</table>
@endforeach

{{-- ── FOOTER ── --}}
<table class="footer-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="footer-left">Greenwave &bull; Channel Partner Evaluation</td>
        <td class="footer-mid">Confidential &mdash; Internal Use Only</td>
        <td class="footer-right">{{ $submittedAt }}</td>
    </tr>
</table>

</body>
</html>