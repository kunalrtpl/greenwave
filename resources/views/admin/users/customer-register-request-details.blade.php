<style>
    .section-block {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f9f9f9;
    }
    .section-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #343a40;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
    }
    .section-table th {
        width: 30%;
        background-color: #f1f1f1;
    }
</style>

<span class="label 
    {{ $request->status == 'Pending' ? 'label-danger' : 
       ($request->status == 'Closed' ? 'label-success' : 
       ($request->status == 'Added' ? 'label-success' : 'label-default')) 
    }} 
    pull-right"
    style="font-size: 14px;">
    {{ ucwords($request->status) }}
</span>
<br><br>
{{-- Business Details --}}
<div class="section-block">
    <div class="section-title">Business Details</div>
    <table class="table table-bordered section-table">
        <tr><th>Business Name</th><td>{{ $request->name ?? 'N/A' }}</td></tr>
        <tr><th>Address</th><td>{{ $request->address ?? 'N/A' }}</td></tr>
        <tr><th>City</th><td>{{ $request->cities ?? 'N/A' }}</td></tr>
        <tr><th>Activity</th><td>{{ $request->activity ?? 'N/A' }}</td></tr>
    </table>
</div>

{{-- Primary User Details --}}
{{-- Primary User Details --}}
<div class="section-block">
    <div class="section-title">Primary User Details</div>
    <div class="row">
        {{-- Left Column: User Details --}}
        <div class="col-md-8">
            <table class="table table-bordered section-table">
                <tr><th>Name</th><td>{{ $request->contact_person_name }}</td></tr>
                <tr><th>Designation</th><td>{{ $request->designation ?? 'N/A' }}</td></tr>
                <tr><th>Phone</th><td>{{ $request->mobile ?? 'N/A' }}</td></tr>
                <tr><th>Email</th><td>{{ $request->email }}</td></tr>
            </table>
        </div>

        {{-- Right Column: Business Card --}}
        <div class="col-md-4 text-center" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;">
            {{-- Business Card One --}}
            @if($request->business_card_url)
                <a href="{{ asset($request->business_card_url) }}" target="_blank">
                    <img src="{{ asset($request->business_card_url) }}" alt="Business Card 1" style="max-height: 160px; border: 1px solid #ccc; padding: 4px;">
                </a>
            @else
                <div class="text-muted" style="font-style: italic;">Business card not uploaded yet.</div>
            @endif

            {{-- Business Card Two --}}
            @if($request->business_card_two)
                <a href="{{ asset($request->business_card_two) }}" target="_blank">
                    <img src="{{ asset($request->business_card_two) }}" alt="Business Card 2" style="max-height: 160px; border: 1px solid #ccc; padding: 4px;">
                </a>
            @else
                <div class="text-muted" style="font-style: italic;">Second business card not uploaded.</div>
            @endif
        </div>
    </div>
</div>



{{-- Business Model & Linking --}}
<div class="section-block">
    <div class="section-title">Business Model & Linking</div>
    <table class="table table-bordered section-table">
        <tr><th>Business Model</th><td>{{ $request->business_model ?? 'N/A' }}</td></tr>
        @if($request->dealer)
            <tr><th>Linked Dealer</th><td>{{ $request->dealer->business_name }}</td></tr>
        @endif
        @if($request->linkedExecutive)
            <tr><th>Linked Executive</th><td>{{ $request->linkedExecutive->name }}</td></tr>
        @endif
    </table>
</div>

{{-- Other Details --}}
<div class="section-block">
    <div class="section-title">Other Details</div>
    <table class="table table-bordered section-table">
        <tr><th>Created By</th><td>{{ $request->creator->name ?? 'N/A' }}</td></tr>
        <tr><th>Created At</th><td>{{ $request->created_at->format('d M Y, h:i A') }}</td></tr>
        <tr><th>Remarks</th><td>{{ $request->employee_remarks }}</td></tr>
        @if(!empty($request->declaration))
            <tr><th>Declaration</th><td>{{ $request->declaration }}</td></tr>
        @endif
        @if($request->is_verify == 1)
            <tr>
                <th>Verified</th>
                <td>
                    <span class="text-success">✔ Verified</span>
                    @if($request->verify_remarks)
                        <br><small class="text-muted">Remarks: {{ $request->verify_remarks }}</small>
                    @endif
                </td>
            </tr>
        @elseif($request->is_verify == 0 && !in_array($request->status, ['Closed', 'Added']))
            <tr>
                <th>Verify</th>
                <td>
                    <form id="verifyForm">
                        @csrf
                        <input type="hidden" name="customer_register_request_id" value="{{$request->id}}">
                        <input type="checkbox" id="verifyCheckbox" required> Verify this request<br><br>
                        <textarea name="verify_remarks" id="verifyRemarks" class="form-control" placeholder="Enter remarks"></textarea>
                        <br>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </form>
                </td>
            </tr>
        @endif
        @if(!empty($request->close_remarks))
            <tr><th>Close Remarks</th><td>{{ $request->close_remarks }}</td></tr>
        @endif
    </table>
</div>
