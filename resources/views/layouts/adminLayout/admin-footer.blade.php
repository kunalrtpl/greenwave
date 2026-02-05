<div class="page-footer">
	<div class="page-footer-inner">
		<?php echo date('Y');?> &copy;  {{config('constants.project_name')}}
	</div>
	<div class="scroll-to-top">
		<i class="icon-arrow-up"></i>
	</div>
</div>
<div class="modal fade" id="PushNotificationModal"  aria-labelledby="PushNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PushNotificationModalLabel">Want to get notification from us?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="allow-push-notification">Allow</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close-push-notification">Deny</button>
            </div>
        </div>
    </div>
</div>
<script src="{!! asset('js/backend_js/admin-script.js') !!}" type="text/javascript"></script>
<script>
jQuery(document).ready(function() {    
   	Metronic.init(); // init metronic core componets
   	Layout.init(); // init layout
   	Demo.init(); // init demo features 
 	Tasks.initDashboardWidget(); // init tash dashboard widget  
});
</script>
<script type="text/javascript">
	$('.select2').select2();
	$(document).on('change','[name=country_name]',function(){
		var country = $(this).val();
		if(country !=""){
			$('.loadingDiv').show();
			$.ajax({
				data : {country:country},
				type : 'GET',
				url : '/admin/get-states',
				success:function(resp){
					$('.loadingDiv').hide();
					$('[name=state_name]').html(resp);
				},
				error:function(){
				}
			})
		}else{
			$('[name=state_name]').html("<option value=>Please Select</option>");
		}
	})

	$(document).on('change','[name=state_name]',function(){
		var state = $(this).val();
		if(state !=""){
			$('.loadingDiv').show();
			$.ajax({
				data : {state:state},
				type : 'GET',
				url : '/admin/get-cities',
				success:function(resp){
					$('.loadingDiv').hide();
					$('[name=city_name]').html(resp);
				},
				error:function(){
				}
			})
		}else{
			$('[name=city_name]').html("<option value=>Please Select</option>");
		}
	})
</script>
<script type="text/javascript">
	function refreshSelect2 (){
		$('.select2').select2();
	    $('.select2').select2({ width: '100%' });
	}
    $(document).on('keypress',function(e) {
        if(e.which == 13) {
            $('.filter-submit').trigger('click');
        }
    });
    $(document).on('change', '[name=product_type], [name=status], [name=business_linking], [name=linked_executive], [name=email_status] , [name=b_card_status], [name=order_type],[name=urgent]', function(e) {
	    $('.filter-submit').trigger('click');
	});

</script>
<script type="text/javascript">
    $(document).on('keypress',function(e) {
        if(e.which == 13) {
            $('.filter-submit').trigger('click');
        }
    });
</script>
<?php $currentUrl =  \Route::getFacadeRoot()->current()->uri(); ?>
@if($currentUrl == 'admin/free-sampling' || $currentUrl =='admin/paid-sampling' || $currentUrl == 'admin/dealer-orders' || $currentUrl == 'admin/direct-customer-orders')
	<script type="text/javascript">
		$(document).ready(function(){
	    	$('.filter-submit').trigger('click');
		});
	</script>
@endif