@php $products = fetchProducts(0); @endphp

<div id="ApplicableDiscountsSpan" style="{{ $style }}">
    <hr class="bold-hr">
    <div class="form-group">
        <label class="col-md-3 control-label">Payment Term </label>
        <div class="col-md-4">
            <input  type="text" placeholder="Payment Term" name="direct_customer_payment_term" style="color:gray" class="form-control" value="{{(!empty($customerdata['payment_term']))?$customerdata['payment_term']: '' }}"/>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-payment_term"></h4>
        </div>
    </div>

    <p><b>Product Linking</b></p>

    <span id="DirectCustomerDiscounts" @if(!empty($customer->business_model) && $customer->business_model == 'Direct Customer') @else style="display: none;" @endif>
        <div class="form-group">
            <label class="col-md-3 control-label">Payment Term</label>
            <div class="col-md-4">
                <input type="text" name="payment_term" class="form-control" style="color:gray"
                       placeholder="Payment Term"
                       value="{{ $customer->payment_term ?? '' }}">
                <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-payment_term"></h4>
            </div>
        </div>
    </span>

    @include('admin.customers.partials.under_discounts')
    @include('admin.customers.partials.under_net_price')

</div>
