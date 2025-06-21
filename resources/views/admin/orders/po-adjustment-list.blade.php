@if(!empty($poDetail['adjust_cancel_items']))
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="portlet blue-hoki box">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-cogs"></i>{{ucwords($poDetail['adjust_cancel_items'][0]['type'])}}  Products
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        Product Name
                                    </th>
                                    <th>
                                        {{ucwords($poDetail['adjust_cancel_items'][0]['type'])}} Qty
                                    </th>
                                    <th>
                                        Reason
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($poDetail['adjust_cancel_items'] as $key => $item)
                                    <tr>
                                        <td>
                                            {{$item['orderitem']['product']['product_name']}}
                                        </td>
                                        <td>
                                            {{$item['qty']}}
                                        </td>
                                        <td>{{$item['reason']}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif