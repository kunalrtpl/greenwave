@if($data['discount'] =="Product Base")
    <div class="form-group">
        <label for="recipient-name" class="col-form-label">Product:</label>
        <select  class="form-control select2" name="product_id" required>
            <option value="">Please Select</option>
            @foreach(products() as $product)
                <option value="{{$product['id']}}">{{$product['product_name']}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="recipient-name" class="col-form-label">From Qty:</label>
        <input step="0.01" placeholder="From Qty" type="number" name="from_qty"  class="form-control">
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Discount-from_qty"></h4>
    </div>
    <div class="form-group">
        <label for="recipient-name" class="col-form-label">To Qty:</label>
        <input step="0.01" placeholder="To Qty" type="number" name="to_qty"  class="form-control">
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Discount-to_qty"></h4>
    </div>
@endif
@if($data['discount'] =="Product Base" || $data['discount'] =="Corporate")
    <div class="form-group">
        <label for="recipient-name" class="col-form-label">Discount (%):</label>
        <input step="0.01" placeholder="Discount" type="number" name="discount"  class="form-control">
    </div>
@endif
<?php /*<div class="form-group">
    <label for="recipient-name" class="col-form-label">Company Share's (%):</label>
    <input placeholder="Company Share" type="number" name="company_share"  class="form-control">
    <h4 class="text-center text-danger pt-3" style="display: none;" id="Discount-company_share"></h4>
</div>
<div class="form-group">
    <label for="recipient-name" class="col-form-label">Dealer Share's (%):</label>
    <p class="form-control" id="DealerShare"></p>
    <input type="hidden" name="dealer_share" >
    <!-- <input placeholder="Dealer Share" type="number" name="dealer_share"  class="form-control">
     -->
    <h4 class="text-center text-danger pt-3" style="display: none;" id="Discount-dealer_share"></h4>
</div>*/?>