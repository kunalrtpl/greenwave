<!-- <div class="form-group">
    <label class="col-md-3 control-label">DP Calculation Cost (Rs.)</label>
    <div class="col-md-4">
        <input type="number" step="0.01" name="dp_calculation_cost" placeholder="DP Calculation Cost"  class="form-control" value="{{(!empty($productdata['dp_calculation_cost']))?$productdata['dp_calculation_cost']: ''}}">
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-dp_calculation_cost"></h4>
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">Company Markup (%)</label>
    <div class="col-md-4">
        <input type="number" step="0.01" name="company_mark_up"  placeholder="Company Markup" class="form-control" value="{{(!empty($productdata['company_mark_up']))?$productdata['company_mark_up']: ''}}">
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-company_mark_up"></h4>
    </div>
</div> -->
<div class="form-group">
    <label class="col-md-3 control-label">Dealer Price (Rs.)</label>
    <div class="col-md-4">
        <input type="number" step="0.01" name="dealer_price" placeholder="Dealer Price"  class="form-control" value="{{(!empty($productdata['dealer_price']))?$productdata['dealer_price']: ''}}">
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-dealer_price"></h4>
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">Freight (Rs.)</label>
    <div class="col-md-4">
        <p class="form-control">Rs. 6</p>
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label"><b>Landed Price (Rs.)</b></label>
    <div class="col-md-4">
        <input type="hidden" name="landed_price" value="{{(!empty($productdata['landed_price']))?$productdata['landed_price']: ''}}">
        <p class="form-control" id="LandedPriceVal">{{(!empty($productdata['landed_price']))?'Rs. '.$productdata['landed_price']: ''}}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">Product Class</label>
    <div class="col-md-4">
        <select class="form-control" name="class">
            <option value="">Please Select</option>
            @foreach(pro_classes() as $class)
                <option value="{{$class['standard']}}">{{$class['class_name']}} ({{$class['from']}}% to {{$class['to']}}%)</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">Dealer Markup (%)</label>
    <div class="col-md-4">
        <input type="number" step="0.01" name="dealer_markup" placeholder="Dealer Markup"  class="form-control" value="{{(!empty($productdata['dealer_markup']))?$productdata['dealer_markup']: ''}}">
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-dealer_markup"></h4>
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label"><b>Market Price (Rs.)</b></label>
    <div class="col-md-4">
        <input type="hidden" name="market_price" value="{{(!empty($productdata['market_price']))?$productdata['market_price']: ''}}">
        <p class="form-control" id="MarketPriceVal">{{(!empty($productdata['market_price']))?'Rs. '.$productdata['market_price']: ''}}</p>
    </div>
</div>