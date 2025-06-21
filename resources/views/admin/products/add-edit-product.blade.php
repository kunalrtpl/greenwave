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
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form id="ProductForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Product Type <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="is_trader_product">
                                            @foreach(product_types() as $pkey=> $protype)
                                                <option value="{{$pkey}}" @if(empty($productdata) ) @if($pkey==0) selected @endif @else @if($productdata['is_trader_product'] ==$pkey) selected @endif @endif>{{$protype}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Physical Form <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="physical_form">
                                            @foreach(physical_forms() as $form=> $physicalForm)
                                                <option value="{{$physicalForm}}" @if(empty($productdata) ) @if($pkey==0) selected @endif @else @if($productdata['physical_form'] ==$physicalForm) selected @endif @endif>{{$physicalForm}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Product Code <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Product Code" name="product_code" style="color:gray" class="form-control" value="{{(!empty($productdata['product_code']))?$productdata['product_code']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-product_code"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Product Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Product Name" name="product_name" style="color:gray" class="form-control" value="{{(!empty($productdata['product_name']))?$productdata['product_name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-product_name"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Product Category <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="product_detail_id">
                                            <option value="">Please Select</option>
                                            @foreach(product_details() as $prodetail)
                                                <?php $getLevel = getProductDetailLevel($prodetail['id']); ?>
                                                <option data-level="{{$getLevel}}" value="{{$prodetail['id']}}" {{(!empty($productdata) && $productdata['product_detail_id'] == $prodetail['id'])?'selected': '' }}>{{$prodetail['name']}}</option>
                                            @endforeach 
                                        </select>
                                        <h6 id="ShowLevel"></h6>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-product_detail_id"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Short Description<!-- <br> (<i>small description upto 100 characters</i>) --><span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <textarea placeholder="Enter Description..." class="form-control" name="short_description">{{(!empty($productdata['short_description']))?$productdata['short_description']: '' }}</textarea>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-short_description"></h4>
                                    </div>
                                </div>
                                <div class="form-group otherProType">
                                    <label class="col-md-3 control-label">Long Description <!-- <br> (<i>detailed paragraph about the product upto 1000 to 2000 characters</i>) --></label>
                                    <div class="col-md-4">
                                        <textarea placeholder="Enter Description..." class="form-control" name="description">{{(!empty($productdata['description']))?$productdata['description']: '' }}</textarea>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-description"></h4>
                                    </div>
                                </div>
                                <div class="form-group otherProType">
                                    <label class="col-md-3 control-label">Suggested Dosage <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <textarea placeholder="Enter Suggested Dosage..."  class="form-control" name="suggested_dosage">{{(!empty($productdata['suggested_dosage']))?$productdata['suggested_dosage']: ''}}</textarea>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-suggested_dosage"></h4>
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                    <label class="col-md-3 control-label"> Show Class <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="show_class">
                                            @foreach(classes() as $form=> $showclass)
                                                <option value="{{$showclass}}" @if(empty($productdata) ) @if($pkey==1) selected @endif @else @if($productdata['show_class'] ==$showclass) selected @endif @endif>{{$showclass}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Show Weightage <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="show_weightage">
                                            @foreach(classes() as $form=> $showWeightage)
                                                <option value="{{$showWeightage}}" @if(empty($productdata) ) @if($pkey==1) selected @endif @else @if($productdata['show_weightage'] ==$showWeightage) selected @endif @endif>{{$showWeightage}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Standard Packing Type <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <?php $packing_types = \App\PackingType::packing_types();
                                        ?>
                                        <select class="form-control select2" name="packing_type_id">
                                            <option value="">Please Select</option>
                                            @foreach($packing_types as $typeInfo)
                                                <option value="{{$typeInfo['id']}}" {{(!empty($productdata) && $productdata['packing_type_id'] == $typeInfo['id'])?'selected': '' }}>{{$typeInfo['name']}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-packing_type_id"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Additional Packing Type (if any)</label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="additional_packing_type_id">
                                            <option value="">Please Select</option>
                                            @foreach($packing_types as $typeInfo)
                                                @if($typeInfo['additional_packing'] == 1)
                                                <option value="{{$typeInfo['id']}}" {{(!empty($productdata) && $productdata['additional_packing_type_id'] == $typeInfo['id'])?'selected': '' }}>{{$typeInfo['name']}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-additional_packing_type_id"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Standard Fill Per Packing <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="number" placeholder="Standard Fill Per Packing" name="standard_fill_size" style="color:gray" class="form-control" value="{{(!empty($productdata['standard_fill_size']))?$productdata['standard_fill_size']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-standard_fill_size"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Standard Order Size <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <?php $packing_sizes = \App\PackingSize::order_sizes();
                                        ?>
                                        <select class="form-control select2" name="packing_size_id">
                                            <option value="">Please Select</option>
                                            @foreach($packing_sizes as $sizeInfo)
                                                <option value="{{$sizeInfo['id']}}" {{(!empty($productdata) && $productdata['packing_size_id'] == $sizeInfo['id'])?'selected': '' }}>{{$sizeInfo['size']}} Kg</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-packing_size_id"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Shelf Life (in months) <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Shelf Life" name="shelf_life" style="color:gray" class="form-control" value="{{(!empty($productdata['shelf_life']))?$productdata['shelf_life']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-shelf_life"></h4>
                                    </div>
                                </div>
                                <!-- <?php $stagesArr = array('Sample Trial Stage','Bulk Trial Stage','Listed Product','Discontinued Product','Re-Introduced Product'); ?>
                                <div class="form-group"  >
                                    <label class="col-md-3 control-label">Product Stage <span class="asteric">*</span></label>
                                    <div class="col-md-9" style="margin-top:8px;">
                                        <table class="table table-bordered" id="StageTable">
                                            <thead>
                                                <th width="50%">Stage</th>
                                                <th>Date</th>
                                            </thead>
                                            @if(isset($productdata['product_stages']))
                                                @foreach($productdata['product_stages'] as $stageInfo)
                                                    <tr>
                                                        <input type="hidden" name="pro_stage[]" value="{{$stageInfo['stage']}}">
                                                        <td>{{$stageInfo['stage']}}</td>
                                                        <td>{{date('d M Y',strtotime($stageInfo['entry_date']))}}</td>
                                                        <input type="hidden" name="pro_stage_date[]" value="{{$stageInfo['entry_date']}}">
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </table>
                                        <input type="button" id="addStage" value="Add New Stage" />
                                    </div>
                                </div> -->
                                <div class="form-group otherProType">
                                    <label class="col-md-3 control-label">Product Introduced on</label>
                                    <div class="col-md-4">
                                        <input  type="date" name="product_introduced_on" style="color:gray" class="form-control" value="{{(!empty($productdata['product_introduced_on']))?$productdata['product_introduced_on']: date('Y-m-d') }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-product_introduced_on"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Recipe No.<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Recipe No" name="lab_recipe_number" style="color:gray" class="form-control" value="{{(!empty($productdata['lab_recipe_number']))?$productdata['lab_recipe_number']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-lab_recipe_number"></h4>
                                    </div>
                                </div>
                                <input type="hidden" name="inherit_type" value="Inhouse">
                                <div id="InheritDiv">
                                    @include('admin.products.product-inhouse')
                                </div> 
                                <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-raw_materials"></h4>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks (if any)</label>
                                    <div class="col-md-4">
                                        <textarea placeholder="(optional)" class="form-control" name="remarks">{{(!empty($productdata['remarks']))?$productdata['remarks']: '' }}</textarea>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-remarks"></h4>
                                    </div>
                                </div>
                                <div class="form-group otherProType">
                                    <label class="col-md-3 control-label">Product Search Keywords (if any)</label>
                                    <div class="col-md-9">
                                        <input  type="text" placeholder="(optional)" name="keywords" style="color:gray" class="form-control" value="{{(!empty($productdata['keywords']))?$productdata['keywords']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-keywords"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($productdata) && $productdata['status'] ==$skey ) checked @else @if($skey ==1) checked @endif @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-status"></h4>
                                    </div>
                                </div>
                            </div>
                            @if(!empty($productdata['id']))
                                <input type="hidden" name="productid" value="{{$productdata['id']}}">
                            @else
                                <input type="hidden" name="productid" value="">
                            @endif
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
<span id="getStageRow" style="display: none;">
    <select class="form-control" name="pro_stage[]" required>
        <option value="">Please Select</option>
        @foreach($stagesArr as $stagedetail)
            <option value="{{$stagedetail}}">{{$stagedetail}}</option>
        @endforeach
    </select>     
</span>

<script type="text/javascript">
   
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

    $(document).on('keyup','.mpPrice',function(){
        var market_price = $(this).val();
        var dealer_price = $(this).closest('td').prev('td').find('input').val();
        var dealer_markup = parseInt(dealer_price) + parseInt(5);
        dealer_markup     = market_price - dealer_markup;
        dealer_markup     = (dealer_markup * 100) / market_price;
        $(this).closest('td').next('td').find('span').text(dealer_markup +'%');
    })
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('click','#addStage',function(){
            $('#StageTable tr:last').after('<tr><td>'+ $('#getStageRow').html() +'</td><td><input class="form-control" type="date" name="pro_stage_date[]" required></td></tr>');
            $('#addStage').hide();
        });

        $(document).on('change','[name=inherit_type]',function(){
            $('.loadingDiv').show();
            var inherit = $(this).val();
            /*if(inherit =="Inhouse"){*/
                $.ajax({
                    url : '/admin/get-product-inherit-layout',
                    data : {inherit_type:inherit},
                    type : 'POST',
                    success:function(resp){
                        $('.loadingDiv').hide();
                        $('#InheritDiv').html(resp.view);
                        refreshSelect2();
                    },
                    error:function(){

                    }
                })
            /*}else{
                $('.loadingDiv').hide();
                $('#InheritDiv').html('');
            }*/

        })

        $(document).on('keyup','.getPercentage',function(){
            $("input:checkbox[name=calculate_rm_cost]").prop("checked", false);
            $('#CalculationDiv').hide();
            $('[name=packing_cost]').val('');
            $('[name=product_cost]').val('');
            $('[name=formulation_cost]').val('');
            $('#ProductCostVal').text('');
            $('[name=company_mark_up]').val('');
            $('[name=dealer_price]').val('');
            $('#DealerPriceVal').text('');
            $('[name=market_price]').val('');
            $('[name=dealer_markup]').val('');
            $('[name=dp_calculation_cost]').val('');
            $('#MarketPriceVal').text('');
        });

        $("#ProductForm").submit(function(e){
            $('.loadingDiv').show();
            e.preventDefault();
            var formdata = new FormData(this);
            $.ajax({
                url: '/admin/save-product',
                type:'POST',
                data: formdata,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('.loadingDiv').hide();
                    if(!data.status){
                        $.each(data.errors, function (i, error) {
                            $('#Product-'+i).addClass('error-triggered');
                            $('#Product-'+i).attr('style', '');
                            $('#Product-'+i).html(error);
                            setTimeout(function () {
                                $('#Product-'+i).css({
                                    'display': 'none'
                                });
                            $('#Product-'+i).removeClass('error-triggered');
                            }, 5000);
                        });
                        $('html,body').animate({
                            scrollTop: $('.error-triggered').first().stop().offset().top - 200
                        }, 1000);
                    }else{
                        window.location.href= data.url;
                    }
                }
            });
        });
    })
</script>
@if(empty($productdata))
<script type="text/javascript">
    $(document).on('keyup','[name=product_code]', function() {
        var code = $(this).val();
        $('[name=product_name]').val(code);
    });
</script>
@endif
<script type="text/javascript">
    $(document).on('change','[name=is_trader_product]',function(){
        var type = $(this).val();
        $('.otherProType').hide();
        if(type === "0"){
            $('.otherProType').show();
        }
    })
    $(document).on('change','.getRawMaterial',function(){
        var rawMaterial = $(this).val();
        if(rawMaterial ==""){
            $(this).parent().parent().find("td:eq(1) input[type=number]").prop("readonly", true);
            $(this).parent().parent().find("td:eq(1) input[type=number]").val('');
        }else{
            $(this).parent().parent().find("td:eq(1) input[type=number]").prop("readonly", false);
        }
    });

    $(document).on('click','#addMoreRawMaterial',function(){
        $('.loadingDiv').show();
        $.ajax({
            url : '/admin/add-more-raw-material',
            type : 'GET',
            success:function(resp){
                $('.loadingDiv').hide();
                $('#AppendRawMaterials').append(resp.view);
                refreshSelect2();
                $('.loadingDiv').hide();
            },
            error:function(){

            }
        })
    })

    $(document).on('click', 'button.removeRow', function () {
        if (confirm("Are you sure you want to delete this?")) {
            $(this).closest('tr').remove();
            return false;
        }
        return false;
    });

    $(document).on('change','[name=calculate_rm_cost]',function(){
        if($('[name=calculate_rm_cost]').is(':checked')){
            $('.loadingDiv').show();
            var params = $("#ProductSearchRow :input").serializeArray();
            $.ajax({
                data : params,
                dataType:"json",
                url : '/admin/calculate-rm-cost',
                type : 'POST',
                success:function(resp){
                    if(!resp.status){
                        alert(resp.message);
                        $('[name=calculate_rm_cost]').prop('checked', false);
                    }else{
                        //document.getElementById("myTableFieldSet").disabled = true;
                        $('#CalculationDiv').show();
                        $('[name=rm_cost]').val(resp.rm_cost);
                        $('#RMCOSTVal').text('Rs. '+resp.rm_cost);
                    }
                    $('.loadingDiv').hide();
                },
                error:function(){
                }
           });
        }else{
            $('#CalculationDiv').hide();
            document.getElementById("myTableFieldSet").disabled = false;
            $('[name=packing_cost]').val('');
            $('[name=product_cost]').val('');
            $('[name=formulation_cost]').val('');
            $('#ProductCostVal').text('');
            $('[name=company_mark_up]').val('');
            $('[name=dealer_price]').val('');
            $('#DealerPriceVal').text('');
            $('[name=market_price]').val('');
            $('[name=dealer_markup]').val('');
            $('[name=dp_calculation_cost]').val('');
            $('#MarketPriceVal').text('');
        }
    });

    $(document).on('keyup','[name=formulation_cost]', function() {
        $('[name=packing_cost]').val('');
        $('[name=product_cost]').val('');
        $('#ProductCostVal').text('');
    });


    $(document).on('keyup','[name=packing_cost]', function() {
        var formulationCost = $('[name=formulation_cost]').val();
        if(formulationCost >0){
            var packingcost = $(this).val();
            var rmcost = $('[name=rm_cost]').val();
            var productcost = parseInt(rmcost) +  parseInt(formulationCost) + parseInt(packingcost);
            if(!isNaN(productcost)) {
                $('[name=product_cost]').val(productcost);
                $('#ProductCostVal').text('Rs. '+productcost);
                updateInhousePricings();
            }
        }else{
            alert('Please enter correct Formulation Cost');
        }
    });

    $(document).on('keyup','[name=product_price]', function() {
        var proPrice = $('[name=product_price]').val();
        $('[name=outsource_packing_cost]').val('');
        $('[name=product_cost]').val(proPrice);
        $('#ProductCostVal').text('Rs. '+proPrice);
    });

    $(document).on('keyup','[name=outsource_packing_cost]', function() {
        var proPrice = $('[name=product_price]').val();
        if(proPrice >0){
            var packingcost = $(this).val();
            var productcost =   parseInt(proPrice) + parseInt(packingcost);
            if(!isNaN(productcost)) {
                $('[name=product_cost]').val(productcost);
                $('#ProductCostVal').text('Rs. '+productcost);
            }
        }else{
            alert('Please enter correct Value');
        }
    });

    function updateInhousePricings(){
        var formulationcost = $('[name=formulation_cost]').val();
        var packingcost = $('[name=packing_cost]').val();
        var rmcost = $('[name=rm_cost]').val();
        var productcost = parseInt(rmcost) +  parseInt(formulationcost) + parseInt(packingcost);
        $('[name=dp_calculation_cost]').val(productcost);
        var dp_calculation_cost = $('[name=dp_calculation_cost]').val();
        if(dp_calculation_cost >0){
            var company_mark_up = $('[name=company_mark_up]').val();
            //Dealer Price = DP Calculation Cost / (1 - (Company Markup/100))
            var dealerprice = parseInt(dp_calculation_cost) / (1- (company_mark_up /100));
            if(!isNaN(dealerprice)) {
                $('[name=dealer_price]').val(Math.round(dealerprice));
                $('#DealerPriceVal').text('Rs. '+Math.round(dealerprice));
            }
        }
        var dealerprice = $('[name=dealer_price]').val();
        if(dealerprice >0){
            var dealer_markup = $('[name=dealer_markup]').val();
            var freight =  5;
            var marketprice = (parseInt(dealerprice) + parseInt(freight)) / (1- (dealer_markup /100));
            if(!isNaN(marketprice) && dealer_markup >0) {
                $('[name=market_price]').val(Math.round(marketprice));
                $('#MarketPriceVal').text('Rs. '+Math.round(marketprice));
            }
        }
    }


    $(document).on('keyup','[name=dp_calculation_cost]', function() {
        $('[name=company_mark_up]').val('');
        $('[name=dealer_price]').val('');
        $('#DealerPriceVal').text('');
        $('[name=market_price]').val('');
        $('[name=dealer_markup]').val('');
        $('#MarketPriceVal').text('');
    });

    $(document).on('keyup','[name=company_mark_up]', function() {
        var dp_calculation_cost = $('[name=dp_calculation_cost]').val();
        if(dp_calculation_cost >0){
            var company_mark_up = $(this).val();
            //Dealer Price = DP Calculation Cost / (1 - (Company Markup/100))
            var dealerprice = parseInt(dp_calculation_cost) / (1- (company_mark_up /100));
            if(!isNaN(dealerprice)) {
                $('[name=dealer_price]').val(Math.round(dealerprice));
                $('#DealerPriceVal').text('Rs. '+Math.round(dealerprice));
            }
            $('[name=market_price]').val('');
            $('[name=dealer_markup]').val('');
            $('#MarketPriceVal').text('');
        }else{
            alert('Please enter correct DP Caclucation Cost');
        }
    });

    $(document).on('keyup','[name=dealer_markup]', function() {
        var dealerprice = $('[name=dealer_price]').val();
        if(dealerprice >0){
            var dealer_markup = $(this).val();
            var freight =  5;
            var marketprice = (parseInt(dealerprice) + parseInt(freight)) / (1- (dealer_markup /100));
            if(!isNaN(marketprice) && dealer_markup >0) {
                $('[name=market_price]').val(Math.round(marketprice));
                $('#MarketPriceVal').text('Rs. '+Math.round(marketprice));
            }
        }else{
            alert('Please enter correct DP Caclucation Cost');
        }
    });
    $(document).on('change','[name=product_detail_id]',function(){
        var value = $(this).find(':selected').attr('data-level');
        $('#ShowLevel').text(value);
    });
    $('[name=product_detail_id]').trigger("change");
</script>
@endsection