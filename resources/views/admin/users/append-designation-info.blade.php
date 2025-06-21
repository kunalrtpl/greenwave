<div class="form-group">
    <label for="recipient-regions" class="col-form-label">Report To : </label>
    <select  class="form-control select2" name="report_to" required>
        <option value="">Please Select</option>
        @foreach($reportingUsers as $reportUser)
            @if($reportUser['id']==1)
                <option value="{{$reportUser['id']}}">{{$reportUser['name']}}</option>
            @else
                <option value="{{$reportUser['id']}}">{{$reportUser['name']}} ({{$reportUser['designation']}})</option>
            @endif
        @endforeach
    </select>
</div>
@if($getDesignationDetails->type =="region")
    <div class="form-group">
        <label for="recipient-regions" class="col-form-label">Region : </label>
        <select  class="form-control getRegions select2" name="regions[]" required @if($getDesignationDetails->multiple_region==1) multiple @endif>
            @if($getDesignationDetails->multiple_region==0)
                <option value="">Please Select</option>
            @endif
            @foreach(regions() as $region)
                <option value="{{$region['id']}}">{{$region['region']}}</option>
            @endforeach
        </select>
        
    </div>
    @if($getDesignationDetails->multiple_region==1)
        <div class="form-group">
            <input type="checkbox" id="SelectAllRegion"> &nbsp;Select All Regions
        </div>
    @endif
    <div class="form-group">
        <label for="recipient-subregions" class="col-form-label">Sub Region : </label>
        <select  class="form-control subRegions @if($getDesignationDetails->having_customer==1) fetchCustomers @endif select2" name="subregions[]" required @if($getDesignationDetails->multiple_sub_region==1) multiple @endif>

        </select>
    </div>
    @if($getDesignationDetails->multiple_sub_region==1)
        <div class="form-group">
            <input type="checkbox" id="SelectAllSubRegion"> &nbsp;Select All Sub Regions
        </div>
    @endif
    @if($getDesignationDetails->having_customer==1)
        <!-- <div class="form-group">
            <label for="recipient-subregions" class="col-form-label">Customers : </label>
            <select  class="form-control getCustomers select2" name="customers[]" required  multiple >

            </select>
        </div> -->
    @endif
@elseif($getDesignationDetails->type =="products")
    <div class="form-group">
        <label for="recipient-products" class="col-form-label">Products : </label>
        <select  class="form-control select2" name="products[]" multiple>
            @foreach(products() as $product)
                <option value="{{$product['id']}}">{{$product['product_code']}}</option>
            @endforeach
        </select>
    </div>
@endif