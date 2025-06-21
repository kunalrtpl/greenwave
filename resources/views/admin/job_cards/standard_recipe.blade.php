<label class="col-md-3 control-label">Standard Recipe </label>
<div class="col-md-9">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>RM</th>
                <th>Qty (%)</th>
                <th>Qty (kg.)</th>
                <th>Available Stock <br> (kg.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rawMaterials as $key => $rmInfo)
            <?php $qty =  ($data['batchsize'] * $rmInfo['percentage_included']/100);?>
            <!-- <input type="hidden" name="rm_ids[]" value="{{$rmInfo['raw_material_id']}}">
            <input type="hidden" name="current_stock[]" value="{{$rmInfo['rawmaterial']['current_stock']}}">
            <input type="hidden" name="percentages[]" value="{{$rmInfo['percentage_included']}}">
            <input type="hidden" name="qtys[]" value="{{$qty}}"> -->
            <tr>
                <td>{{$rmInfo['rawmaterial']['name']}}</td>
                <td>{{$rmInfo['percentage_included']}} %</td>
                <td>{{$qty}}</td>
                <td>{{$rmInfo['rawmaterial']['current_stock']}}</td>
                
            </tr>
            @endforeach
            <tr>
                <td></td>
                <td><b>100%</b></td>
                <td><b>{{$data['batchsize']}}</b></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheet-rm_requirements"></h4>
</div>
</div>
<style type="text/css">
.rmWrap {
    width: 70%;
    margin: 0 auto;
    text-align: center;
}
.marginBtm{
    margin-bottom: 2rem !important;
}
</style>
<label class="col-md-3 marginBtm control-label">Add Requirements </label>
<div class="rmWrap">
    <div class="row" id="appendRmRequirements">
        
    </div>
    <div class="row">
        <table class="table table-stripped table-bordered" id="TotalqtyTable" style="display: none;">
            <tr>
                <th colspan="6" class="text-right" id="qtyPercentage"></th>
                <td width="10%"></td>
            </tr>
        </table>
    </div>
    <div class="row">
        <div class="col-md-4">
           <div class="">
                <select class="form-control" id="RequirementType">
                    <option selected value="">Select Type</option>
                    <option value="RM">RM</option>
                    <option value="SRM">SRM</option>
                    <!-- <option value="FP">FP</option> -->
                </select>
            </div>
        </div>
        <div class="col-md-4" style="display: none;">
           <div class="" id="appendTypeList" >
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn green" id="addRmRequirement" type="button">Add</button>
        </div>
    </div>
</div>