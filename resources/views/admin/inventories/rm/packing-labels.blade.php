<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
        * {
        box-sizing: border-box;
        }
        body {
        font-family: Arial, Helvetica, sans-serif;
        }
        /* Float four columns side by side */
        .column {
        float: left;
        width:33%;
        padding: 0 10px;
        margin-bottom:20px;
        }
        .column .card h3
        {
            font-size:40px;
            margin:0;
        }
        /* Remove extra left and right margins, due to padding */
        .row {margin: 0 -5px;}
        /* Clear floats after the columns */
        .row:after {
        content: "";
        display: table;
        clear: both;
        }
        /* Responsive columns */
        @media screen and (max-width: 600px) {
        .column {
        width: 100%;
        display: block;
        margin-bottom: 20px;
        }
        }
        /* Style the counter cards */
        .card {
        /*box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);*/
        padding: 16px;
        text-align: center;
        /*background-color: #f1f1f1;*/
        border:1px solid #000;
        }
        .s-title
        {
            display:inline-block;
            width:90%;
        }
        .print-btn
        {
            display:inline-block;
        }
        </style>
    </head>
    <body>
        <!-- <h4 class="s-title">Total Packing Lables: {{$rminvDetails['no_of_packs']}}</h4> -->
        <h4 class="s-title">QC Approved RM Packing Label</h4>
        <button class="print-btn" onclick="window.print()">Print this page</button>
        <div class="row">
            <?php $rminvDetails['no_of_packs'] =1; ?>
            @for($i=1; $i<=$rminvDetails['no_of_packs']; $i++)
                <div class="column">
                    <div class="card">
                        <h3>{{$rminvDetails['rawmaterial']['coding']}} </h3>
                        @if(isset($rminvDetails['rm_history'][0]))
                            <p>({{$rminvDetails['serial_no']}}, {{date('d/M/Y',strtotime($rminvDetails['rm_history'][0]['created_at']))}})</p>
                        @endif
                        <h2>Batch NO : {{$rminvDetails['batch_no']}} </h2>
                    </div>
                </div>
            @endfor
        </div>
    </body>
</html>