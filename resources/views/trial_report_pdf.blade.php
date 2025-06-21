<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Report</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
        body {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
             font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th,
        td {
            border: 1px solid #ddd;
             padding: 4px 0 0 0;
            text-align: left;
            font-size: 8px;
            
        }
           .infoTable th {
            border: none;
            padding: 6px 4px 4px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: 500;
        }
        .infoTable td {
            border: none;
            padding: 4px 0 0 0;
            text-align: left;
            font-size: 8px;
        }

        th {
            background-color: #f2f2f2;
               font-size: 8px;
            font-weight: 500;
             padding: 6px 4px 4px 4px;
        }

        h2 {
            text-align: left;
            margin: 0;
            font-size: 14px;
        }
         h3 {
             font-size: 14px;
            text-align: left;
            margin: 0;
            font-weight: 600;
        }
        .infoTable{
            width: 50%;
        }
        .infoTable tr td{
            width: 50%;
        }
    </style>
</head>

<body>

    <h2>Trial Report</h2>
    <table class="infoTable">
        <tr>
            <th colspan="2">Basic Details</th>
        </tr>
        <tr>
            <td>Date</td>
            <td>@if(!empty($trialReport['trial_report_date']))
                    {{date('d/M/Y',strtotime($trialReport['trial_report_date']))}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Customer Name</td>
            <td>
                @if(isset($trialReport['customer']['name']))
                    {{$trialReport['customer']['name']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Ref No.</td>
            <td>
                @if(!empty($trialReport['feedback_id']))
                    {{$trialReport['feedback_id']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Trial Objective</td>
            <td>
                @if(!empty($trialReport['trial_objective']))
                    {{$trialReport['trial_objective']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Trial Conducted By</td>
            <td>Name of the person conducting trial</td>
        </tr>
    </table>
    <table class="infoTable">
        <tr>
            <th colspan="2">Lot Details</th>
        </tr>
        <tr>
            <td>Substrate/Count</td>
            <td>
                @if(!empty($trialReport['substrate_count']))
                    {{$trialReport['substrate_count']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>GSM/GUM</td>
            <td>200</td>
        </tr>
        <tr>
            <td>Lot No.</td>
            <td>
                @if(!empty($trialReport['lot_no']))
                    {{$trialReport['lot_no']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Lot Size</td>
            <td>
                @if(!empty($trialReport['lot_size']))
                    {{$trialReport['lot_size']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Shade</td>
            <td>
                @if(!empty($trialReport['shade']))
                    {{$trialReport['shade']}}
                @endif
            </td>
        </tr>
    </table>

    <table class="infoTable">
        <tr>
            <th colspan="2">Machine Details</th>

        </tr>
        <tr>
            <td>Process Type</td>
            <td>
                @if(!empty($trialReport['process_type']))
                    {{$trialReport['process_type']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Machine Type</td>
            <td>
                @if(!empty($trialReport['machine_type']))
                    {{$trialReport['machine_type']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Machine No.</td>
            <td>
                @if(!empty($trialReport['machine_no']))
                    {{$trialReport['machine_no']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Machine Make</td>
            <td>
                @if(!empty($trialReport['machine_make']))
                    {{$trialReport['machine_make']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Operator Name</td>
            <td>
                @if(!empty($trialReport['operator_name']))
                    {{$trialReport['operator_name']}}
                @endif
            </td>
        </tr>
       
    </table>
    <table class="infoTable">
        <tr>
            <th colspan="2">Padding Mangle Parameters</th>
        </tr>
        
        <tr>
            <td>Fabric Pick-up %</td>
            <td>
                @if(!empty($trialReport['fabric_pick_up']))
                    {{$trialReport['fabric_pick_up']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Trough Loss (%)</td>
            <td>
                @if(!empty($trialReport['trough_loss']))
                    {{$trialReport['trough_loss']}}
                @endif
            </td>
        </tr>
        <tr>
            <td>Total Trough Solution Required</td>
            <td>
                @if(!empty($trialReport['solution_required_in_trough']))
                    {{$trialReport['solution_required_in_trough']}}
                @endif
            </td>
        </tr>
    </table>

    <h3>Initial Precautions</h3>
    <table>
        <tr>
            <th>S.No.</th>
            <th>Bath Description</th>
            <th>Product Description</th>
            <th>Product Name</th>
            <th>Dosage <br>(%/GPL)</th>
            <th>Dosage <br>(kg)</th>
            @if($costshow == 1)
                <th>Gross Price</th>
                <th>Net Price</th>
                <th>Cost</th>
            @endif
            <th>Product Application Details</th>
        </tr>
        @foreach($trialReport['baths'] as $bkey=>  $bath)
            <?php ++$bkey;?>
            @if(isset($bath['products']) && !empty($bath['products']))
                @foreach($bath['products'] as $pkey=>  $product)
                    @if($pkey == 0)
                    <tr>
                        <td rowspan="{{count($bath['products'])}}">{{$bkey}}</td>
                        <td rowspan="{{count($bath['products'])}}">{{$bath['description']}}</td>
                        <td>{{$product['product_description']}}</td>
                        <td>{{$product['product_name']}}</td>
                        <td>{{$product['dosage']}}%</td>
                        <td>{{$product['dosage_kg']}}</td>
                        @if($costshow == 1)
                            <td>{{$product['gross_price']}}</td>
                            <td>{{$product['net_price']}}</td>
                            <td>{{$product['cost']}}</td>
                        @endif
                        <td>{{$product['application_details']}}</td>
                    </tr>
                    @else
                        <tr>
                            <td>{{$product['product_description']}}</td>
                            <td>{{$product['product_name']}}</td>
                            <td>{{$product['dosage']}}%</td>
                            <td>{{$product['dosage_kg']}}</td>
                            @if($costshow == 1)
                                <td>{{$product['gross_price']}}</td>
                                <td>{{$product['net_price']}}</td>
                                <td>{{$product['cost']}}</td>
                            @endif
                            <td>{{$product['application_details']}}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr>
                    <td>{{$bkey}}</td>
                    <td>{{$bath['description']}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    @if($costshow == 1)
                        <td></td>
                        <td></td>
                        <td></td>
                    @endif
                    <td></td>
                </tr>
            @endif
            <tr>
                <th colspan="10">Bath Application Details : {{$bath['application_details']}}</th>
            </tr>
        @endforeach
    </table>
</body>
</html>