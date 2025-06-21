<div class="modal fade" id="BatchTrackingModal" tabindex="-1" role="dialog" aria-labelledby="BatchTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="BatchTrackingModalLabel">Tracking Details</h5>
            </div>
            <div class="modal-body" id="BatchTrackingDetails">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click','.batchtracking',function(){
        $('.loadingDiv').show();
        $('#BatchTrackingDetails').html('');
        var batchsheetid =  $(this).data('batchsheetid');
        $.ajax({
            data : {batchsheetid:batchsheetid},
            url : '/admin/batchsheet/batch-tracking',
            type : 'POST',
            success:function(resp){
                $('#BatchTrackingDetails').html(resp);
                $('#BatchTrackingModal').modal('show');
                $('.loadingDiv').hide();
            }
        })
    })
</script>