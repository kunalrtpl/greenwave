<?php use App\Designation; ?>
<?php $depts = departments();?>
<style type="text/css">
    .select2-container--open{
        z-index:9999999         
}
</style>
<div class="modal fade" id="UserDeptModal" role="dialog" aria-labelledby="UserDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="AddUserDeptDesgForm" action="javascript:;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="UserDeptModalLabel">Add User Department </h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Department :</label>
                        <select  class="form-control select2" name="department_id" required>
                            <option value="">Please Select</option>
                            @foreach($depts as $deptinfo)
                                <option value="{{$deptinfo['id']}}">{{$deptinfo['department']}}</option>
                            @endforeach
                        </select> 
                    </div>
                    <div id="DesignationDetails">
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>