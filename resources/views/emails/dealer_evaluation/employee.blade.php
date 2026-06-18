<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Channel Partner Evaluation</title>
<style>
  body{margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;}
  .wrap{width:100%;background:#f4f6f8;padding:30px 0;}
  .box{max-width:620px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);}

  /* Header — green for new, amber for update */
  .hdr-new    {background:#1a7f3c;padding:28px 40px;text-align:center;}
  .hdr-update {background:#b45309;padding:28px 40px;text-align:center;}
  .hdr-new h1, .hdr-update h1 {color:#fff;margin:0;font-size:20px;letter-spacing:.5px;}
  .hdr-new p  {color:#c8e6c9;margin:6px 0 0;font-size:13px;}
  .hdr-update p {color:#fde68a;margin:6px 0 0;font-size:13px;}

  .body{padding:32px 40px;color:#333;}
  .body h2{font-size:16px;margin-top:0;}
  .h2-new    {color:#1a7f3c;}
  .h2-update {color:#b45309;}
  .body p{font-size:14px;line-height:1.7;margin:0 0 12px;}
  table{width:100%;border-collapse:collapse;margin:16px 0;}
  td{padding:9px 12px;font-size:13px;border-bottom:1px solid #eee;}
  td:first-child{color:#666;font-weight:bold;width:42%;}
  td:last-child{color:#222;}

  /* Badge — green for new, amber for update */
  .badge-new    {display:inline-block;background:#e8f5e9;color:#1a7f3c;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:bold;}
  .badge-update {display:inline-block;background:#fef3c7;color:#b45309;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:bold;}

  /* Note — yellow for new, amber for update */
  .note-new    {background:#fff8e1;border-left:4px solid #f9a825;padding:12px 16px;border-radius:4px;font-size:13px;color:#555;margin:18px 0;}
  .note-update {background:#fff7ed;border-left:4px solid #b45309;padding:12px 16px;border-radius:4px;font-size:13px;color:#555;margin:18px 0;}

  .ftr{background:#f9f9f9;padding:18px 40px;text-align:center;font-size:11px;color:#999;border-top:1px solid #eee;}
</style>
</head>
<body>
<div class="wrap">
  <div class="box">

    {{-- ── HEADER ── --}}
    @if($is_new ?? true)
      <div class="hdr-new">
        <h1>Prospective Channel Partner Evaluation</h1>
        <p>New Prospective Dealer Form Submitted</p>
      </div>
    @else
      <div class="hdr-update">
        <h1>Greenwave &mdash; Channel Partner Evaluation</h1>
        <p>Existing Dealer Evaluation Updated</p>
      </div>
    @endif

    {{-- ── BODY ── --}}
    <div class="body">

      @if($is_new ?? true)
        <h2 class="h2-new">New Evaluation Received</h2>
      @else
        <h2 class="h2-update">Evaluation Updated</h2>
      @endif

      <p>Dear <strong>{{ $employee['name'] ?? 'Team' }}</strong>,</p>

      @if($is_new ?? true)
        <p>A <strong>new</strong> Prospective Channel Partner Evaluation has been received. Details below &mdash; full form attached as PDF.</p>
      @else
        <p>An <strong>existing</strong> Channel Partner Evaluation has been <strong>updated</strong> by <strong>{{ $submittedBy['name'] ?? 'an executive' }}</strong>. The revised details are below &mdash; updated form attached as PDF for your review.</p>
      @endif

      <table>
        <tr><td>Firm Name</td><td><strong>{{ $dealer['business_name'] }}</strong></td></tr>
        <tr><td>Contact Person</td><td>{{ $dealer['name'] ?? '—' }}</td></tr>
        <tr><td>Mobile</td><td>{{ $dealer['owner_mobile'] }}</td></tr>
        <tr><td>Email</td><td>{{ $dealer['email'] ?: '—' }}</td></tr>
        <tr><td>City</td><td>{{ $dealer['city'] }}</td></tr>
        <tr>
          <td>Source of Lead</td>
          <td>
            @if($is_new ?? true)
              <span class="badge-new">{{ $dealer['source_of_lead'] ?? '—' }}</span>
            @else
              <span class="badge-update">{{ $dealer['source_of_lead'] ?? '—' }}</span>
            @endif
          </td>
        </tr>
        <tr><td>{{ ($is_new ?? true) ? 'Submitted By' : 'Updated By' }}</td><td>{{ $submittedBy['name'] ?? '—' }}</td></tr>
        <tr><td>{{ ($is_new ?? true) ? 'Submitted At' : 'Updated At' }}</td><td>{{ $submittedAt }}</td></tr>
      </table>

      @if($is_new ?? true)
        <div class="note-new">
          &#128206; The complete evaluation form is attached as a PDF. Please review and take action.
        </div>
      @else
        <div class="note-update">
          &#9998; This evaluation has been revised. Please review the updated PDF attached and take appropriate action.
        </div>
      @endif

      <p>— Greenwave System</p>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="ftr">
      <p>Automated message from Greenwave Partner Management System. Do not reply.</p>
    </div>

  </div>
</div>
</body>
</html>