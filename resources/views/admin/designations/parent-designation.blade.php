<?php use App\Designation; ?>
<div class="form-group">
    <label class="col-md-3 control-label">Parent Dept. Designation <span class="asteric">*</span></label>
    <div class="col-md-4">
        <select class="form-control select2" name="parent_id">
            <option value="ROOT">ROOT</option>
            @if(!empty($designationdata) && isset($designationdata['parent_id']))
                <?php $selected_designation =$designationdata['parent_id']; ?>
            @else
                <?php $selected_designation ="ROOT"; ?>
            @endif
            <?php 
                
                Designation::DesignationTree($designationdata['department_id'],'ROOT','',$selected_designation); ?>
        </select>
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Designation-parent_id"></h4>
    </div>
</div>