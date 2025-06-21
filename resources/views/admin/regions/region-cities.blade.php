<div class="form-group">
    <label class="col-md-3 control-label">Cities <span class="asteric">*</span></label>
    <div class="col-md-6">
        <select class="form-control getCities select2" name="cities[]" multiple required>
            @foreach($cities as $city)
                <option value="{{ $city['city_name'] }}"
                    @if(!empty($selCities) && in_array($city['city_name'], $selCities)) selected @endif>
                    {{ $city['city_name'] }}
                </option>
            @endforeach
        </select>
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Region-cities"></h4>
    </div>
</div>
