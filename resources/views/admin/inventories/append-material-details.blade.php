@if($data['type']=="RM")
    <div class="form-group">
        <label class="col-md-3 control-label">RM Name<span class="asteric">*</span></label>
        <div class="col-md-4">
            <select class="form-control select2" name="raw_material_id">
                <option value="">Please Select</option>
                    @foreach(rawmaterials() as $rmInfo)
                        <option value="{{$rmInfo['id']}}">{{$rmInfo['name']}}</option>
                    @endforeach
            </select>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-raw_material_id"></h4>
        </div>
    </div>
   <!--  <div class="form-group">
        <label class="col-md-3 control-label">No. of Packs <span class="asteric">*</span></label>
        <div class="col-md-4">
            <input  type="number" placeholder="No. of Packs" name="no_of_packs" style="color:gray" class="form-control" required />
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-no_of_packs"></h4>
        </div>
    </div> -->
    <div class="form-group">
        <label class="col-md-3 control-label">Qty (in Kgs.) <span class="asteric">*</span></label>
        <div class="col-md-4">
            <input  type="number" step="0.001" placeholder="Qty" name="stock" style="color:gray" class="form-control"/>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-stock"></h4>
        </div>
    </div> 
   <!--  <div class="form-group">
        <label class="col-md-3 control-label">Price <span class="asteric">*</span></label>
        <div class="col-md-4">
            <input  type="number" placeholder="Price" name="raw_material_price" style="color:gray" class="form-control" required />
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-raw_material_price"></h4>
        </div>
    </div> -->
    <div class="form-group">
        <label class="col-md-3 control-label">Batch No. </label>
        <div class="col-md-4">
            <input  type="text" placeholder="Batch No." name="supplier_batch_no" style="color:gray" class="form-control" required />
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-supplier_batch_no"></h4>
        </div>
    </div>
@elseif($data['type'] == "PM")
	<div class="form-group">
        <label class="col-md-3 control-label">Packing Type <span class="asteric">*</span></label>
        <div class="col-md-4">
            <?php $packing_types = \App\PackingType::packing_types();
            ?>
            <select class="form-control select2" name="packing_type_id" required>
                <option value="">Please Select</option>
                @foreach($packing_types as $packingType)
                    <option value="{{$packingType['id']}}">{{$packingType['name']}}</option>
                @endforeach
            </select>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-packing_type_id"></h4>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Qty<span class="asteric">*</span></label>
        <div class="col-md-4">
            <input  type="number" placeholder="Qty" name="stock" style="color:gray" class="form-control"/>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-stock"></h4>
        </div>
    </div> 
@elseif($data['type'] == "PL")
    <div class="form-group">
        <label class="col-md-3 control-label">Packing Label <span class="asteric">*</span></label>
        <div class="col-md-4">
            <?php $labels = \App\Label::labels();
            ?>
            <select class="form-control select2" name="label_id" required>
                <option value="">Please Select</option>
                @foreach($labels as $label)
                    <option value="{{$label['id']}}">{{$label['label_type']}}</option>
                @endforeach
            </select>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-label_id"></h4>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Qty<span class="asteric">*</span></label>
        <div class="col-md-4">
            <input  type="number" placeholder="Qty" name="stock" style="color:gray" class="form-control"/>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-stock"></h4>
        </div>
    </div> 
@elseif($data['type'] =="SRM" || $data['type'] =="RFDM")
    <div class="form-group">
        <label class="col-md-3 control-label">Product<span class="asteric">*</span></label>
        <div class="col-md-4">
            <?php $product_types = product_types() ?>
            <select class="form-control select2" name="product_id">
                <option data-product_type="" value="">Please Select</option>
                @foreach(ospProducts() as $productInfo)
                    <option data-product_type="{{$product_types[$productInfo['is_trader_product']]}}" value="{{$productInfo['id']}}">{{$productInfo['product_name']}}</option>
                @endforeach
            </select>
            <span id="AppendProductType"></span>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-product_id"></h4>
        </div>
    </div>
    <!-- <div class="form-group">
        <label class="col-md-3 control-label">No. of Packs <span class="asteric">*</span></label>
        <div class="col-md-4">
            <input  type="number" placeholder="No. of Packs" name="no_of_packs" style="color:gray" class="form-control" required />
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-no_of_packs"></h4>
        </div>
    </div> -->
    <div class="form-group">
        <label class="col-md-3 control-label">Qty (in Kgs.) <span class="asteric">*</span></label>
        <div class="col-md-4">
            <input  type="number" step="0.001" placeholder="Qty" name="stock" style="color:gray" class="form-control"/>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-stock"></h4>
        </div>
    </div> 
    
    <!-- <div class="form-group">
        <label class="col-md-3 control-label">Price <span class="asteric">*</span></label>
        <div class="col-md-4">
            <input  type="number" placeholder="Price" name="raw_material_price" style="color:gray" class="form-control" required />
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-raw_material_price"></h4>
        </div>
    </div> -->
    <div class="form-group">
        <label class="col-md-3 control-label">Batch No. </label>
        <div class="col-md-4">
            <input  type="text" placeholder="Batch No." name="supplier_batch_no" style="color:gray" class="form-control" required/>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-supplier_batch_no"></h4>
        </div>
    </div>
    @if($data['type'] =="RFDM")
        <div class="form-group">
            <label class="col-md-3 control-label">Attach COA </label>
            <div class="col-md-4">
                <input class="form-control" type="file" name="coa">
                <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-coa"></h4>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">Attach QC Report </label>
            <div class="col-md-4">
                <input class="form-control" type="file" name="qc_report">
                <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-qc_report"></h4>
            </div>
        </div>
    @endif
    @if($data['type'] =="RFDM")
        <table class="table table-bordered table-stripped">
            <tr>
                <th colspan="4" style="font-size: 15px; background-color: #D3D3D3; text-align: center;border-right: 5px solid;">PACKING DETAILS</th>
                <th colspan="2" style="font-size: 15px; text-align: center; background-color: #bdd6ee;border-right: 5px solid;">PACKING CONSUMPTION</th>
                <th colspan="2" style="font-size: 15px; text-align: center; background-color: #ffe599;">LABEL CONSUMPTION</th>
            </tr>
            <tr>
                <th width="20%" style="text-align: center; background-color:#EEEEEE;">Packing Type</th>
                <th width="10%" style="text-align: center; background-color:#EEEEEE;">No. of Packs</th>
                <th width="10%" style="text-align: center; background-color:#EEEEEE;">Fill Size</th>
                <th width="10%" style="text-align: center; background-color:#EEEEEE; border-right: 5px solid;">Total Material Filled</th>
                <th width="10%" style="text-align: center; background-color:#deeaf6;">Packing Type</th>
                <th width="10%" style="text-align: center; background-color:#deeaf6;border-right: 5px solid;">No. of Packs Consumed</th>
                <th width="15%" style="text-align: center; background-color:#fff2cc;">Label Type</th>
                <th style="text-align: center; background-color:#fff2cc;">No. of Labels Consumed</th>
            </tr>
            <tr>
                <td>
                    <?php $packing_types = \App\PackingType::packing_types();
                    ?>
                    <select class="form-control finalPackingType" name="final_packing_type" required>
                        <option value="">Please Select</option>
                        @foreach($packing_types as $packingType)
                            <option data-tare_weight="{{$packingType['tare_weight']}}" data-stock="{{$packingType['stock']}}" value="{{$packingType['id']}}">{{$packingType['name']}}</option>
                        @endforeach
                    </select>
                    <p class="text-center"></p>
                </td>
                <td>
                    <input placeholder="No. of Packs" type="number" name="final_no_of_packs" class="form-control finalNoOfPacks" required>
                </td>
                <td>
                    <input placeholder="Net Fill Size" type="number" name="final_net_fill_size" class="form-control finalNetFillSize" required>
                    <p class="text-center"></p>
                </td>
                <td style="border-right: 5px solid;">
                    <p class="text-center"></p>
                </td> 
                <td>
                    <p class="text-center"></p>
                </td>
                <td style="border-right: 5px solid;">
                    <input placeholder="No. of Packs Consumed" type="number" name="packs_consumed" class="form-control" required>
                </td>
                <td>
                    <select class="form-control" name="label_id" required>
                        <option value="">Please Select</option>
                    </select>
                </td>
                <td>
                    <input placeholder="No. of Labels Consumed" type="number" name="labels_consumed" class="form-control" required>
                </td>
            </tr>
        </table>
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-final_packing_errors"></h4>
        <hr class="bold-hr">
    @endif
@endif