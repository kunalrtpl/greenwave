<div class="modal fade" id="QtyDiscountModal" tabindex="-1" role="dialog" aria-labelledby="QtyDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="QtyDiscountModalLabel">QTY Discounts</h5>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <table class="table table-striped table-bordered table-hover">
                        <tr>
                            <th>From - To (Qty.)</th>
                            <th>Discount</th>
                        </tr>
                        @if(!empty($qtyDiscounts))
                            @foreach($qtyDiscounts as $dis)
                                <tr>
                                    <td>{{$dis['range_from']}} - {{$dis['range_to']}}</td>
                                    <td>{{$dis['discount']}}%</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="2">
                                    <p class="text-center">No discounts found.</p>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
