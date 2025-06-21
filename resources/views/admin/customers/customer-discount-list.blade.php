<?php $from_qty = 0; $to_qty =0; $product_id = 0; $discount =0;?>
<tr>
    <td>
        @if($discountinfo['discount_type'] == "Corporate")
            Exclusive Corporate Discount
        @else
            Special Product Discount
        @endif
    </td>
    <td>
        @if(isset($discountinfo['product_id']) && !empty(isset($discountinfo['product_id'])))
            <?php $product_id = $discountinfo['product_id'];
                $details = productinfo($product_id);
            ?>
            {{$details['product_code']}}
        @endif
    </td>
    <td>
        @if(isset($discountinfo['from_qty']) && !empty($discountinfo['from_qty']))
            <?php $from_qty =  $discountinfo['from_qty']; 
            $to_qty =  $discountinfo['to_qty'];
            ?>
            {{$discountinfo['from_qty']}} - {{$discountinfo['to_qty']}}
        @endif
    </td>
    <?php /*<td>
        {{$discountinfo['company_share']}}%
    </td>
    <td>
        {{$discountinfo['dealer_share']}}%
    </td>
    <td>
        {{$discountinfo['company_share'] + $discountinfo['dealer_share']}}%
    </td>*/?>
    @if(isset($discountinfo['discount']) && !empty($discountinfo['discount']))
        <?php $discount =  $discountinfo['discount'];?>
    @endif
    <td>
        {{$discount}}%
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-danger removeRow" href="javascript:;">
            <i class="fa fa-times"></i>
        </button>
    </td>
    <?php 
        $custDisJson['discount_type']  =  $discountinfo['discount_type'];
        //$custDisJson['committed_sale_qty'] = $committed_sale_qty;
        $custDisJson['from_qty'] = $from_qty;
        $custDisJson['to_qty'] = $to_qty;
        $custDisJson['product_id']         = $product_id;
        //$custDisJson['company_share'] = $discountinfo['company_share'];
        $custDisJson['discount'] = $discount;
        //$custDisJson['dealer_share'] = $discountinfo['dealer_share'];
    ?>
    <input type="hidden" name="customer_discounts[]" value="{{json_encode($custDisJson)}}">
</tr>