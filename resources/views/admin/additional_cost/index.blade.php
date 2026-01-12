@extends('layouts.adminLayout.backendLayout')

@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Additional Cost</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="portlet blue-hoki box">
                    <div class="portlet-title">
                        <div class="caption">
                            Select Product
                        </div>
                    </div>

                    <div class="portlet-body form">
                        <form method="GET"
                          action="{{ route('admin.additional-cost.preview', 0) }}"
                          target="_blank"
                          class="form-horizontal"
                          onsubmit="this.action=this.action.replace('/0','/' + this.product_id.value);">

                        <div class="form-body">
                            <div class="form-group">
                                <label class="col-md-3 control-label">
                                    Product <span class="asteric">*</span>
                                </label>
                                <div class="col-md-4">
                                    <select name="product_id"
                                            class="form-control select2"
                                            required>
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->product_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions text-center">
                            <button type="submit" class="btn green">
                                Preview Additional Cost
                            </button>
                        </div>
                    </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
