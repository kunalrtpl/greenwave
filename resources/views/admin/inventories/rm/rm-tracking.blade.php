<div class="modal fade" id="RmTrackingModal" tabindex="-1" role="dialog" aria-labelledby="RmTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="RmTrackingModalLabel">Tracking Details</h5>
            </div>
            <div class="modal-body" id="RMTrackingDetails">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click','.rmtracking',function(){
        $('.loadingDiv').show();
        $('#RMTrackingDetails').html('');
        var rminvid =  $(this).data('rminvid');
        $.ajax({
            data : {rminvid:rminvid},
            url : '/admin/inventory/material-tracking',
            type : 'POST',
            success:function(resp){
                $('#RMTrackingDetails').html(resp);
                $('#RmTrackingModal').modal('show');
                $('.loadingDiv').hide();
            }
        })
    })
</script>