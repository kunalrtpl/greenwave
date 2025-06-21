<div class="form-group">
    <label class="col-md-3 control-label">Product Weightage</label>
    <div class="col-md-9">
        <fieldset id="myTableFieldSet">
            <table id="ProWeightageTable" class="table table-hover table-bordered table-striped">
                <tbody>
                    <tr>
                        <th width="30%">Month</th>
                        <th width="20%">Year</th>
                        <th width="20%">Weightage</th>
                        <th width="10%">Actions</th>
                    </tr>
                    @if(!empty($productdata) && !empty($productdata['weightages']))
                    @foreach($productdata['weightages'] as $key=> $proWeightage)
                    <input type="hidden" name="weightage_ids[{{$key}}]" value="{{$proWeightage['id']}}">
                    <tr>
                        <td>
                            {{date('M',strtotime($proWeightage['start_date']))}}
                            <input type="hidden" name="weightage_months[]" value="{{$proWeightage['month']}}">
                        </td>
                        <td>
                            {{date('Y',strtotime($proWeightage['start_date']))}}
                            <input type="hidden" name="weightage_years[]" value="{{$proWeightage['year']}}">
                        </td>
                        <td>
                            @if(date('m') == $proWeightage['month'])
                                <input type="number" step="0.01" name="weightages[]" placeholder="Weightage"  class="form-control"required value="{{$proWeightage['weightage']}}">
                            @else
                                {{$proWeightage['weightage']}}
                                <input type="hidden" name="weightages[]" value="{{$proWeightage['weightage']}}">
                            @endif
                        </td>
                        <td>
                            <input type="checkbox" name="is_delete_weightages[{{$key}}]" value="{{$proWeightage['id']}}"> Delete
                        </td>
                    </tr>
                    @endforeach
                    @else
                    @endif
                </tbody>
            </table>
            <input type="button" id="weightageAddRow" value="Add More" />
        </fieldset>
    </div>
</div>