<tr class="blockIdWrap">
    <td>
        <select class="form-control getRawMaterial select2" name="raw_material_ids[]" required>
            <option value="">Please Select</option>
            @foreach(rawmaterials() as $rawmaterial)
            <option value="{{$rawmaterial['id']}}">{{$rawmaterial['name']}} (Price: Rs. {{$rawmaterial['price']}})</option>
            @endforeach
        </select>
    </td>
    <td>
        <input step="0.01" type="number" class="form-control getPercentage" name="percentage[]" placeholder="Enter Percentage Cost" readonly>
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-danger removeRow" href="javascript:;">
            <i class="fa fa-times"></i>
        </button>
    </td>
</tr>