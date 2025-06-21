@extends('layouts.adminLayout.backendLayout')
@section('content')

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Products Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/products') }}">Products </a>
            </li>
        </ul>
        <div class="row">
            @if(Session::has('flash_message_success'))
                <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
            @endif
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form  role="form" class="form-horizontal" method="post" action="{{url('/admin/product-costing/'.$productid)}}" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div id="CalculationDiv">
                                    <!-- <div class="form-group">
                                        <label class="col-md-3 control-label">Formulation Cost (Rs.)</label>
                                        <div class="col-md-4">
                                            <input type="number" step="0.01" name="formulation_cost" placeholder="Formulation Cost"  class="form-control" value="{{(!empty($productdata['formulation_cost']))?$productdata['formulation_cost']: ''}}">
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-formulation_cost"></h4>
                                        </div>
                                    </div> -->
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Packing Cost (Rs.)</label>
                                        <div class="col-md-4">
                                            <p class="form-control">{{(!empty($productdata['packing_cost']))?$productdata['packing_cost']: ''}}</p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">MOQ (kg)</label>
                                        <div class="col-md-4">
                                            <input type="number" step="0.01" name="moq"  placeholder="MOQ" class="form-control" value="{{($productdata['moq'] !="")?$productdata['moq']: ''}}">
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-moq"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Average Dispatch Time</label>
                                        <div class="col-md-4">
                                            <input  type="number"  step="0.01"placeholder="Average Dispatch Time" name="average_dispatch_time" style="color:gray" class="form-control" value="{{(!empty($productdata['average_dispatch_time']))?$productdata['average_dispatch_time']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-average_dispatch_time"></h4>
                                        </div>
                                    </div>
                                    @if($productdata['is_trader_product'] ==0)
                                    <!-- <div class="form-group">
                                        <label class="col-md-3 control-label">Allowed Free Sample Qty (kg)</label>
                                        <div class="col-md-4">
                                            <input  type="number"  step="0.01"placeholder="Allowed Free Sample Qty" name="free_sample_unit" style="color:gray" class="form-control" value="{{(!empty($productdata['free_sample_unit']))?$productdata['free_sample_unit']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-free_sample_unit"></h4>
                                        </div>
                                    </div> -->
                                    @else
                                        <div class="form-group">
                                        <label class="col-md-3 control-label">Dealer Price</label>
                                        <div class="col-md-4">
                                            <input  type="number"  step="0.01" placeholder="Dealer Price" name="dealer_price" style="color:gray" class="form-control" value="{{(!empty($productdata['dealer_price']))?$productdata['dealer_price']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-dealer_price"></h4>
                                        </div>
                                    </div>
                                    @endif
                                    @if($productdata['is_trader_product'] ==0)
                                    @include('admin.products.product-weightages')
                                    <hr class="bold-hr">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Market Price Calculator </label>
                                        <div class="col-md-9">
                                            <div class="panel-group" id="accordion">
  <div class="panel panel-default">
    <div class="panel-heading text-center">
         <!-- <div style="background-color: #E5E5E5;color:#fff;" class="panel-heading text-center"> -->
      <h4 class="panel-title">
        <a style="text-decoration:none;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
        </a>
      </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse">
      <div class="panel-body">
        @include('admin.products.product-pricing')
      </div>
    </div>
  </div>
 
  </div>
                                        </div>
                                    </div>
        
                                    
                                </div>
                                @endif
                                @if($productdata['is_trader_product'] ==0)
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Dealer/ Market Pricings </label>
                                    <div class="col-md-9">
                                        <fieldset id="myTableFieldSet">
                                            <table id="MPDPPriceTable" class="table table-hover table-bordered table-striped">
                                                <tbody>
                                                    <tr>
                                                        <th width="30%">Dealer Price</th>
                                                        <th width="20%">Market Price</th>
                                                        <th width="20%">Dealer Markup</th>
                                                        <th width="5%">Product Class</th>
                                                        <th width="30%">Date</th>
                                                        <th width="10%">Actions</th>
                                                    </tr>
                                                    @if(!empty($productdata) && !empty($productdata['pricings']))
                                                    @foreach($productdata['pricings'] as $key=> $proPricing)
                                                    <input type="hidden" name="pricing_ids[]" value="{{$proPricing['id']}}">
                                                    <tr>
                                                        <td>
                                                            Rs. {{$proPricing['dealer_price']}}
                                                        </td>
                                                        <td>
                                                            Rs. {{$proPricing['market_price']}}
                                                        </td>
                                                        <td>{{$proPricing['dealer_markup']}}%</td>
                                                        <?php $class = geClass($proPricing['dealer_markup']) ?>
                                                        <td>{{$class}}</td>
                                                        <td>
                                                            {{date('d M Y',strtotime($proPricing['price_date']))}}
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" name="is_delete[{{$key}}]" value="{{$proPricing['id']}}">Delete
                                                            <!-- @if($proPricing['price_date'] >= date('Y-m-d')) -->
                                                            <!-- @endif -->
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    @else
                                                    <tr>
                                                        <td>
                                                            <input type="number" class="form-control" name="dp_prices[]" placeholder="Dealer Price" value="" min="0" value="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="this.parentNode.parentNode.style.backgroundColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'inherit':'gray'" >
                                                        </td>
                                                        <td><input type="number" class="form-control" name="mp_prices[]" placeholder="Market Price" value="" min="0" value="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="this.parentNode.parentNode.style.backgroundColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'inherit':'gray'" ></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>
                                                            <input type="date" class="form-control" name="dates[]" >
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                            <input type="button" id="mpdprow" value="Add More" />
                                        </fieldset>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="form-actions right1 text-center">
                                <button class="btn green" type="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<table class="tmpl" id="WeightageRow" style="display: none;">
    <tr>
        <td>
            <select class="form-control" name="weightage_months[]" required=""> 
                <option value="01">Jan</option>
                <option value="02">Feb</option>
                <option value="03">March</option>
                <option value="04">Apirl</option>
                <option value="05">May</option>
                <option value="06">June</option>
                <option value="07">July</option>
                <option value="08">Aug</option>
                <option value="09">Sept</option>
                <option value="10">Oct</option>
                <option value="11">Nov</option>
                <option value="12">Dec</option>
            </select>
        </td>
        <td>
            <select class="form-control" name="weightage_years[]" required>
                @for($i=2023;$i<= date('Y');$i++)
                    <option value="{{$i}}">{{$i}}</option>
                @endfor
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="weightages[]" placeholder="Weightage"  class="form-control"required >
        </td>
        <td></td>
    </tr>
</table>
<script type="text/javascript">
    $(document).on('click','#weightageAddRow',function(){
        var tr = $('#WeightageRow').children().html();
        console.log(tr);
        $('#ProWeightageTable tr:last').after(tr);

    })
    $(document).on('keyup','.dpPrice',function(){
        var dealer_price = $(this).val();
        /*console.log(dealer_price);
        var market_price = $(this).closest('td').next('td').find('input').val();
        var dealer_markup = parseInt(dealer_price) + parseInt(5);
        dealer_markup     = market_price - dealer_markup;
        dealer_markup     = (dealer_markup * 100) / market_price;
        console.log(dealer_markup);
        console.log($(this).next('td:eq(1)').find('span').text());
        $(this).closest('td').find("td:eq(2)").html(dealer_markup);*/
    })
       $(document).on('click', '#mpdprow', function() {
        @if(isset($productdata['pricings'][0]['price_date']))
            var minDate = "<?php echo $productdata['pricings'][0]['price_date']; ?>"; // Passing PHP variable to JS
        @else
            var minDate = "<?php echo date('Y-m-d'); ?>"; // Passing PHP variable to JS
        @endif
        
        $('#MPDPPriceTable tr:last').after(`
            <tr>
                <td><input type="number" class="form-control dpPrice" name="dp_prices[]" placeholder="Dealer Price" required min="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="this.parentNode.parentNode.style.backgroundColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'inherit':'gray'"></td>
                <td><input type="number" class="form-control mpPrice" name="mp_prices[]" placeholder="Market Price" min="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="this.parentNode.parentNode.style.backgroundColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'inherit':'gray'"></td>
                <td><span></span></td>
                <td><span></span></td>
                <td><input type="date" class="form-control" name="dates[]" min="` + minDate + `"></td>
            </tr>
        `);
    });

    $(document).on('keyup','[name=dealer_price]', function() {
        var dealerprice = $('[name=dealer_price]').val();
        $('#MarketPriceVal').text('');
        $('[name=dealer_markup]').val('');
        var freight =  6;
        var landedPrice = (parseInt(dealerprice) + parseInt(freight));
        $('[name=landed_price]').val(Math.round(landedPrice));
        $('#LandedPriceVal').text('Rs. '+Math.round(landedPrice));
    });

    $(document).on('keyup','[name=dealer_markup]', function() {
        var dealerprice = $('[name=dealer_price]').val();
        if(dealerprice >0){
            var dealer_markup = $(this).val();
            calMarketPrice(dealer_markup);
        }else{
            alert('Please enter correct Dealer Price');
        }
    });
    $(document).on('change','[name=class]',function(){
        var dealer_markup = $(this).val();
        if(dealer_markup ==""){
            $('#MarketPriceVal').text('');
            $('[name=dealer_markup]').val('');
        }else{
            $('[name=dealer_markup]').val(dealer_markup);
            calMarketPrice(dealer_markup);
        }
    })
    function calMarketPrice(dealer_markup){
        var dealerprice = $('[name=dealer_price]').val();
        var freight =  6;
        var marketprice = (parseInt(dealerprice) + parseInt(freight)) / (1- (dealer_markup /100));
        if(!isNaN(marketprice) && dealer_markup >0) {
            $('[name=market_price]').val(Math.round(marketprice));
            $('#MarketPriceVal').text('Rs. '+Math.round(marketprice));
        }
    }
</script>

<style>
    .panel-heading .accordion-toggle:after {
    /* symbol for "opening" panels */
    font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
    content: "\e114";    /* adjust as needed, taken from bootstrap.css */
    float: left;        /* adjust as needed */
    color: #4a8c17 !important;         /* adjust as needed */
}
.panel-heading .accordion-toggle.collapsed:after {
    /* symbol for "collapsed" panels */
    content: "\e080";    /* adjust as needed, taken from bootstrap.css */
}
.panel-default>.panel-heading {
    background-color: transparent !important;
    height: 40px;
}
.panel-heading .accordion-toggle:after
{
    color:#fff;
}
.panel-title>a:hover
{
    color:#fff;
}

</style>
@endsection