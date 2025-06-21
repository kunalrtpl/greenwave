<div class="form-group">
    <label class="col-md-3 control-label">Recipe <span class="asteric">*</span></label>
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
                                <input step="0.01" type="number" class="form-control getPercentage" name="percentage[]" placeholder="Enter Percentage Cost" readonly>
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