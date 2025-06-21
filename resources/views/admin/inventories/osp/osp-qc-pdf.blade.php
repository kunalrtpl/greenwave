<?php use App\CustomFunction; ?>
<!DOCTYPE html>
<html>
	<head>
		<style>
		table {
		font-family: arial, sans-serif;
		border-collapse: collapse;
		width: 100%;
		}
		td, th {
		border: 1px solid #dddddd;
		text-align: left;
		padding: 8px;
		}
		tr:nth-child(even) {
		background-color: #dddddd;
		}
		h5{
			font-family: arial, sans-serif;
		}
		h2{
			font-family: arial, sans-serif;
		}
		* {
		  box-sizing: border-box;
		}
		.column {
		  float: left;
		  width: 100%;
		  padding: 5px;
		}
		/* Clearfix (clear floats) */
		.row::after {
		  content: "";
		  clear: both;
		  display: table;
		}
		table tbody tr td {
		  font-size: 11.5px;
		}
		table tbody tr td {
		  font-size: 10.5px;
		}
		table thead tr th {
		  font-size: 10.5px;
		}
		</style>
	</head>
	<body>
		<h5>Incoming OSP Details</h5>
		<table>
            <tr>
            	<th>Serial No</th>
                <th>No. of Samples</th>
                <th>No. of Packs</th>
                <th>Qty (kgs)</th>
                <th>Code</th>
                <th>Sent to Lab</th>
            </tr>
            <tr>
                <td>{{$ospdetails['serial_no']}}</td>
                <td>{{$ospdetails['no_of_samples']}}</td>
                <td>{{$ospdetails['no_of_packs']}}</td>
                <td>{{$ospdetails['stock']}}</td>
                <td>{{$ospdetails['product']['product_code']}}</td>
                <td>{{date('d M Y h:iA',strtotime($ospdetails['osp_history'][0]['created_at']))}}<br>
                    ({{$ospdetails['osp_history'][0]['updateby']['name']}})
                </td>
            </tr>
        </table>
        @for($i=1; $i<= $ospdetails['no_of_samples']; $i++)
        	<h5>QC Criteria for Sample {{$i}}</h5>
	        <table class="table table-bordered">
	            <tr>
	                <th width="35%">#</th>
	                <th width="20%">Master Range</th>
	                <th>Range</th>
	                <th>Remarks</th>
	            </tr>
	            @if($type=="filled")
	            	@foreach($ospdetails['ospinv_checklists'] as $ospinvchecklist)
	            		@if($ospinvchecklist['sample_no'] == $i)
			                <tr>
			                    <td>
			                        {{$ospinvchecklist['checklist']['name']}}
			                    </td>
			                    <td>
			                        {{$ospinvchecklist['product_range']}}
			                    </td>
			                    <td>
			                    	{{$ospinvchecklist['range']}}
			                    </td>
			                    <td>
			                    	{{$ospinvchecklist['remarks']}}
			                    </td>
			                </tr>
			            @endif
		            @endforeach
	            @else
					@foreach($ospdetails['osp_checklists'] as $checklist)
		                <tr>
		                    <td>
		                        {{$checklist['checklist']['name']}}
		                    </td>
		                    <td>
		                        {{$checklist['range']}}
		                    </td>
		                    <td></td>
		                    <td> </td>
		                </tr>
	            	@endforeach
	            @endif
	        </table>
		@endfor
	</body>
</html>