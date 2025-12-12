<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Greenwave</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style type="text/css">
    @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap');

    body { font-family: 'Open Sans', Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; table-layout: fixed; }
    th, td { padding: 6px 4px; font-size: 12px; border-bottom: 1px solid #dee2e6; word-wrap: break-word; text-align:left; }
    th { background-color: #B2C85B; color:#fff; font-size:14px; font-weight:500; }
    tr:nth-child(even) td { background:#e9ecef; }
    .address-area { text-align:center; padding-bottom:20px; }
    .gst-text { text-align:right; margin-top:10px; font-style:italic; font-size:8px; }
    .footerBottom { background:#f1f1f1; padding:14px; text-align:center; margin-top:40px; }
    .footerBottom p { font-size:12px; margin:0; }
    .social span { margin:10px; font-size:14px; font-weight:700; }
    .boxes span { width:100%; height:30px; display:inline-block; }
  </style>
</head>

<body>

  <div class="content">
    <div class="table-data">

      <!-- HEADER LOGO -->
      <div class="address-area">
        <img width="250px" src="./images/pdf_logo.png">
      </div>

      <span style="display:block;text-align:center;font-size:20px;font-weight:600;">Product Type</span>
      @if(!empty($data['product_category_title']))
          <span style="display:block; text-align:left; font-size:16px; font-weight:600;"><strong>Product Category :</strong>
              {{ $data['product_category_title'] }}
          </span>
      @endif
      <!-- DYNAMIC TABLE -->
      <table>
        <!-- HEADER -->
        <tr>
          @foreach($data['columns'] as $col)
              <th>{{ $col }}</th>
          @endforeach
        </tr>

        <!-- BODY ROWS (VALUES ONLY, NO KEYS REQUIRED) -->
        @foreach($data['rows'] as $row)
          <tr>
              @foreach($row as $value)
                  <td>{!! nl2br(e($value)) !!}</td>
              @endforeach
          </tr>
        @endforeach
      </table>

      <!-- FOOTER -->
      <span class="gst-text">G.S.T. Extra as Applicable</span>

      <div class="footerBottom">
        <p>1103, Lodha Supremus, District Gate 2, Kolshet Road, Thane West, Thane 400607</p>

        <div class="social">
          <span>E-mail: info@greenwaveglobal.com</span>
          <span>Contact: +91 22 46007542</span>
        </div>

        <div class="boxes">
          <span style="background-color:#B2C85B;"></span>
        </div>
      </div>

    </div>
  </div>

</body>
</html>
