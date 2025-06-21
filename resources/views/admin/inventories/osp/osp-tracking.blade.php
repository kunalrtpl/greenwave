<div class="modal fade" id="OSPTrackingModal" tabindex="-1" role="dialog" aria-labelledby="OSPTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="OSPTrackingModalLabel">Tracking Details</h5>
            </div>
            <div class="modal-body" id="OSPTrackingDetails">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click','.OSPtracking',function(){
        $('.loadingDiv').show();
        $('#OSPTrackingDetails').html('');
        var ospinvid =  $(this).data('ospinvid');
        $.ajax({
            data : {ospinvid:ospinvid},
            url : '/admin/inventory/osp-tracking',
            type : 'POST',
            success:function(resp){
                $('#OSPTrackingDetails').html(resp);
                $('#OSPTrackingModal').modal('show');
                $('.loadingDiv').hide();
            }
        })
    })
</script>