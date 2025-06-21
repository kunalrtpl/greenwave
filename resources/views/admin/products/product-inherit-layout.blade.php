@if($productdata['inherit_type'] =="Inhouse")
<!-- <div class="form-group">
        <label class="col-md-3 control-label">Batch Out Duration <span class="asteric">*</span></label>
        <div class="col-md-4">
            <input type="number" name="batch_out_duration" placeholder="Batch Out Duration"  class="form-control" value="{{(!empty($productdata['batch_out_duration']))?$productdata['batch_out_duration']: ''}}">
        </div>
    </div> -->
<div class="form-group">
    <label class="col-md-3 control-label">Raw Material <span class="asteric">*</span></label>
    <div class="col-md-8">
        <fieldset id="myTableFieldSet">
            <table id="ProductSearchRow" class="table table-hover table-bordered table-striped">
                <tbody id="AppendRawMaterials">
                    <tr>
                        <th width="50%">Raw Material</th>
                        <th width="30%">Percent (%) Included</th>
                        <th width="20%">Actions</th>
                    </tr>
                    <?php $raw_Materials = rawmaterials() ?>
                    @if(isset($productdata['raw_materials']) && !empty($productdata['raw_materials']))
                        @foreach($productdata['raw_materials'] as $rmkey=> $rawMaterial)
                            <tr class="blockIdWrap">
                                <td>
                                <select class="form-control getRawMaterial select2" name="raw_material_ids[]" required>
                                    <option value="">Please Select</option>
                                    @foreach($raw_Materials as $rawmaterial)
                                        <option value="{{$rawmaterial['id']}}" @if($rawMaterial['raw_material_id']== $rawmaterial['id']) selected @endif>{{$rawmaterial['name']}} (Price: Rs. {{$rawmaterial['price']}})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" class="form-control getPercentage" name="percentage[]" placeholder="Enter Percentage Cost" value="{{$rawMaterial['percentage_included']}}" min="0" value="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="this.parentNode.parentNode.style.backgroundColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'inherit':'red'">
                            </td>
                            <td>
                                @if($rmkey >0)  
                                    <button type="button" class="btn btn-sm btn-danger removeRow" href="javascript:;">
                                        <i class="fa fa-times"></i>
                                    </button>
                                @endif 
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr class="blockIdWrap">
                            <td>
                                <select class="form-control getRawMaterial select2" name="raw_material_ids[]" required>
                                    <option value="">Please Select</option>
                                    @foreach($raw_Materials as $rawmaterial)
                                        <option value="{{$rawmaterial['id']}}">{{$rawmaterial['name']}} (Price: Rs. {{$rawmaterial['price']}})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" class="form-control getPercentage" name="percentage[]" placeholder="Enter Percentage Cost" readonly>
                            </td>
                            <td></td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <input type="button" id="addMoreRawMaterial" value="Add More" />
        </fieldset>
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">Calculate RM COST <span class="asteric">*</span></label>
    <div class="col-md-4">
        <p style="margin-top: 8px;">
            <input type="checkbox"  name="calculate_rm_cost" value="1" {{(!empty($productdata['rm_cost']))?'checked': ''}}>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-calculate_rm_cost"></h4>
        </p>
    </div>
</div>
<div id="CalculationDiv" @if(!empty($productdata) && !empty($productdata['rm_cost']))  @else style="display: none;" @endif>
    <hr class="bold-hr">
    <div class="form-group">
        <label class="col-md-3 control-label">RM Cost (Rs.)<span class="asteric">*</span></label>
        <div class="col-md-4">
            <input type="hidden" name="rm_cost" value="{{(!empty($productdata['rm_cost']))?$productdata['rm_cost']: ''}}">
            <p class="form-control" id="RMCOSTVal">{{(!empty($productdata['rm_cost']))?'Rs.'. $productdata['rm_cost']: ''}}</p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Formulation Cost (Rs.)<span class="asteric">*</span></label>
        <div class="col-md-4">
            <input type="number" step="0.01" name="formulation_cost" placeholder="Formulation Cost"  class="form-control" value="{{(!empty($productdata['formulation_cost']))?$productdata['formulation_cost']: ''}}">
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-formulation_cost"></h4>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Packing Cost (Rs.)<span class="asteric">*</span></label>
        <div class="col-md-4">
            <input type="number" step="0.01" name="packing_cost"  placeholder="Packing Cost" class="form-control" value="{{(!empty($productdata['packing_cost']))?$productdata['packing_cost']: ''}}">
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-packing_cost"></h4>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Total Product Cost (Rs.)<span class="asteric">*</span></label>
        <div class="col-md-4">
            <input type="hidden" name="product_cost" value="{{(!empty($productdata['total_product_cost']))?$productdata['total_product_cost']: ''}}">
            <p class="form-control" id="ProductCostVal">{{(!empty($productdata['total_product_cost']))?'Rs. '.$productdata['total_product_cost']: ''}}</p>
        </div>
    </div>
    <hr class="bold-hr">
    @include('admin.products.product-pricing')
</div>
@else
    <div class="form-group">
        <label class="col-md-3 control-label">Product Price (Rs.)<span class="asteric">*</span></label>
        <div class="col-md-4">
            <input type="number" name="product_price"  placeholder="Product Price" class="form-control" value="{{(!empty($productdata['product_price']))?$productdata['product_price']: ''}}">
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-product_price"></h4>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Packing Cost (Rs.)</label>
        <div class="col-md-4">
            <input type="number" name="outsource_packing_cost"  placeholder="Packing Cost" class="form-control" value="{{(!empty($productdata['packing_cost']))?$productdata['packing_cost']: ''}}">
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-outsource_packing_cost"></h4>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Total Product Cost (Rs.)<span class="asteric">*</span></label>
        <div class="col-md-4">
            <input type="hidden" name="product_cost" value="{{(!empty($productdata['total_product_cost']))?$productdata['total_product_cost']: ''}}">
            <p class="form-control" id="ProductCostVal">{{(!empty($productdata['total_product_cost']))?'Rs. '.$productdata['total_product_cost']: ''}}</p>
        </div>
    </div>
    <hr class="bold-hr">
    @include('admin.products.product-pricing')
@endif
