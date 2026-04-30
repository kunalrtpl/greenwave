<div class="inline-dept-details-wrap">

    {{-- Report To --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group inline-dept-group">
                <label class="inline-dept-label">
                    <i class="fa fa-user-circle-o"></i> Report To
                </label>
                <select class="form-control select2" name="report_to" required>
                    <option value="">Please Select</option>
                    @foreach($reportingUsers as $reportUser)
                        @if($reportUser['id'] == 1)
                            <option value="{{ $reportUser['id'] }}">{{ $reportUser['name'] }}</option>
                        @else
                            <option value="{{ $reportUser['id'] }}">{{ $reportUser['name'] }} ({{ $reportUser['designation'] }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($getDesignationDetails->type == "region")

        {{-- Divider --}}
        <div class="inline-dept-divider">
            <span><i class="fa fa-map"></i> Region Assignment</span>
        </div>

        <div class="row">
            {{-- Region --}}
            <div class="col-md-6">
                <div class="form-group inline-dept-group">
                    <div class="inline-dept-label-row">
                        <label class="inline-dept-label">
                            <i class="fa fa-globe"></i> Region
                        </label>
                        @if($getDesignationDetails->multiple_region == 1)
                            <label class="inline-dept-check-label">
                                <input type="checkbox" id="SelectAllRegion" class="inline-dept-check">
                                <span>Select All</span>
                            </label>
                        @endif
                    </div>
                    <select class="form-control getRegions select2" name="regions[]"
                            @if($getDesignationDetails->multiple_region == 1) multiple @endif required>
                        @if($getDesignationDetails->multiple_region == 0)
                            <option value="">Please Select</option>
                        @endif
                        @foreach(regions() as $region)
                            <option value="{{ $region['id'] }}">{{ $region['region'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Sub Region --}}
            <div class="col-md-6 subRegionGroup">
                <div class="form-group inline-dept-group">
                    <div class="inline-dept-label-row">
                        <label class="inline-dept-label">
                            <i class="fa fa-map-pin"></i> Sub Region
                        </label>
                        @if($getDesignationDetails->multiple_sub_region == 1)
                            <label class="inline-dept-check-label">
                                <input type="checkbox" id="SelectAllSubRegion" class="inline-dept-check">
                                <span>Select All</span>
                            </label>
                        @endif
                    </div>
                    <select class="form-control subRegions @if($getDesignationDetails->having_customer == 1) fetchCustomers @endif select2"
                            name="subregions[]"
                            @if($getDesignationDetails->multiple_sub_region == 1) multiple @endif required>
                    </select>
                    <div class="inline-dept-hint">
                        <i class="fa fa-info-circle"></i> Select a region first to load sub-regions
                    </div>
                </div>
            </div>
        </div>

    @elseif($getDesignationDetails->type == "products")

        {{-- Divider --}}
        <div class="inline-dept-divider">
            <span><i class="fa fa-cubes"></i> Product Assignment</span>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="form-group inline-dept-group">
                    <label class="inline-dept-label">
                        <i class="fa fa-cube"></i> Products
                    </label>
                    <select class="form-control select2" name="products[]" multiple>
                        @foreach(products() as $product)
                            <option value="{{ $product['id'] }}">{{ $product['product_code'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

    @endif

</div>

<style>
/* ── Inline Department Details — Premium Style ────────────────── */
.inline-dept-details-wrap {
    padding: 4px 0 0;
}

/* Label row with inline "Select All" checkbox */
.inline-dept-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.inline-dept-label {
    display: flex !important;
    align-items: center;
    gap: 6px;
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #3a3f51 !important;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 0 !important;
}
.inline-dept-label i {
    color: #00897b;
    font-size: 13px;
}

/* Select All checkbox pill */
.inline-dept-check-label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #e8f5f3;
    border: 1px solid #b2dfdb;
    border-radius: 12px;
    padding: 2px 10px 2px 6px;
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #00897b !important;
    cursor: pointer;
    margin-bottom: 0 !important;
    white-space: nowrap;
    transition: background .15s;
}
.inline-dept-check-label:hover { background: #c8ece8; }
.inline-dept-check {
    width: 14px !important;
    height: 14px !important;
    cursor: pointer;
    accent-color: #00897b;
    margin: 0 !important;
}

/* Form group spacing */
.inline-dept-group {
    margin-bottom: 16px !important;
}
.inline-dept-group .form-control {
    border-color: #cdd4dc;
    border-radius: 4px;
    font-size: 13px;
    color: #444;
}
.inline-dept-group .form-control:focus {
    border-color: #00897b;
    box-shadow: 0 0 0 2px rgba(0,137,123,.15);
}

/* Hint text under sub region */
.inline-dept-hint {
    font-size: 11px;
    color: #aaa;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.inline-dept-hint i { color: #00897b; }

/* Section divider */
.inline-dept-divider {
    display: flex;
    align-items: center;
    margin: 10px 0 16px;
    gap: 10px;
}
.inline-dept-divider::before,
.inline-dept-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e0e6ef;
}
.inline-dept-divider span {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #00897b;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 5px;
}
.inline-dept-divider span i { font-size: 12px; }

/* Fix select2 inside this area */
.inline-dept-details-wrap .select2-container { width: 100% !important; }

/* Hide hint once sub regions are loaded */
.subRegions:not(:empty) ~ .inline-dept-hint { display: none; }
</style>