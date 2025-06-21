<style type="text/css">
    .select2-container--open{
        z-index:9999999         
}
</style>
<div class="modal fade" id="CustDisModal" role="dialog" aria-labelledby="CustDisModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="AddCustDisForm" action="javascript:;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="CustDisModalLabel">Add Discount </h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Discount Type :</label>
                        <select  class="form-control select2" name="discount_type" required>
                            
                        </select>
                    </div>
                    <div id="AppendDisDetails">
                        
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