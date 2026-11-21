@extends('layouts.adminLayout.backendLayout')
@section('content')
@include('admin.sampling.partials.styles')
<div class="page-content-wrapper">
   <div class="page-content">
      <ul class="page-breadcrumb breadcrumb" style="margin-bottom:14px;">
         <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
         <li><a href="{{ url('/admin/entry/rfdm') }}">Finished Product Entries</a><i class="fa fa-circle"></i></li>
         <li><span>{{ $title }}</span></li>
      </ul>

      @if(isset($_GET['m']))
         <div role="alert" class="alert alert-success alert-dismissible fade in"><button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> {!! $_GET['m'] !!} </div>
      @endif

      {{-- The sampling module's design system (admin.sampling.partials.styles)
           is reused here so this screen and Sample Dispatch read as one product
           rather than two. The bridge at the bottom of this file maps the
           theme's form markup onto it, which is why the appended partial for
           each material type keeps working untouched. --}}
      <div class="sdx" id="invPage">
         <div class="sdx-head">
            <div>
               <h1>{{ $title }}</h1>
               <div class="sdx-meta">Record finished product arriving into the plant &mdash; it is credited to incoming stock against the batch you enter here.</div>
            </div>
            <a class="sdx-btn ghost sm" href="{{ url('/admin/entry/rfdm') }}"><i class="fa fa-arrow-left"></i> Back to list</a>
         </div>

         <form id="InventoryForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />

            <div class="sdx-card">
               <div class="sdx-ch">
                  <div class="t">
                     <span class="ix">1</span>
                     <div>Material Received<small>What arrived, how much of it, and the batch it came in</small></div>
                  </div>
                  <span class="sdx-chip ok">{{ getMaterialTypes()[defaultMaterialType()] }}</span>
               </div>
               <div class="sdx-cb">
                  <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Incoming Date<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input type="date" class="form-control" name="incoming_date" value="{{date('Y-m-d')}}">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-incoming_date"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Type<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <?php
                                            $materialArr = getMaterialTypes();
                                            // The only type this screen offers today.
                                            $defaultType = defaultMaterialType();
                                        ?>
                                        <select class="form-control" name="type">
                                            {{--
                                               ============================================================
                                               ONLY Finished Product (RFD) IS OFFERED - DO NOT DELETE THE
                                               REST. The client asked for the other material types to be
                                               hidden for now; they are kept here, switched off, because
                                               they are wanted back later.

                                               getMaterialTypes() still returns all of them and every other
                                               screen still uses the full list - nothing was taken out of
                                               the helper, so RM / PM / PL / SRM keep working wherever else
                                               they are used.

                                               TO BRING THEM BACK: delete the single hard-coded option
                                               below and the "if (false)" line under it, plus its matching
                                               "endif" (both marked "hidden options").
                                               ============================================================
                                            --}}
                                            <option value="{{ $defaultType }}" selected>{{ $materialArr[$defaultType] }}</option>

                                            @if(false) {{-- hidden options: delete this line to restore --}}
                                                <option value="">Please Select</option>
                                                @foreach($materialArr as $mkey=> $type)
                                                    <option value="{{$mkey}}" {{ $mkey == $defaultType ? 'selected' : '' }}>{{$type}}</option>
                                                @endforeach
                                            @endif {{-- hidden options: delete this line to restore --}}
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-type"></h4>
                                    </div>
                                </div>
                                <div id="AppendIncomingTypeDetails">
                                    
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks </label>
                                    <div class="col-md-4">
                                        <textarea   placeholder="Remarks..." name="remarks" style="color:gray" class="form-control"></textarea>
                                    </div>
                                </div> 
                            </div>
               </div>
            </div>

            <div class="sdx-actions">
               <div class="note"><i class="fa fa-info-circle"></i> Saving credits this qty to the product's incoming stock and writes a ledger entry against the batch.</div>
               <a class="sdx-btn ghost" href="{{ url('/admin/entry/rfdm') }}">Cancel</a>
               <button class="sdx-btn g" type="submit"><i class="fa fa-check"></i> Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>
<style type="text/css">
/* ──────────────────────────────────────────────────────────────────────
   Add Incoming Material — bridge onto the sampling design system.

   The look lives in admin/sampling/partials/styles.blade.php (the .sdx
   tokens Sample Dispatch uses). Nothing is redefined here: this only maps
   the theme's form markup onto those same tokens, because the fields are
   injected by append-material-details.blade.php as
   "label.col-md-3 + div.col-md-4" rows and that partial must keep working
   for every material type, including the ones switched off today.

   Every name, id and class the AJAX depends on is untouched
   (#AppendIncomingTypeDetails, #Inventory-*, .select2, .form-group).
   ────────────────────────────────────────────────────────────────────── */

/* The theme's rows become sdx fields, three to a line. */
#invPage .form-body {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px 20px;
}

/* So the injected rows join that grid instead of sitting a level below it. */
#invPage #AppendIncomingTypeDetails { display: contents; }

#invPage .form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
    margin: 0;
}

/* Batch No. sits on the same line as Qty, taking the two remaining columns
   so its "already used" warning has room to read on one line.
   Remarks takes the whole row.

   Both are matched twice on purpose: by position with a child combinator,
   which works anywhere, and by field with :has(), which keeps holding if
   the order of the injected rows ever changes. */
#invPage .form-body > .form-group:last-child,
#invPage .form-group:has(textarea) { grid-column: 1 / -1; }

#invPage #AppendIncomingTypeDetails > .form-group:last-child,
#invPage .form-group:has([name="supplier_batch_no"]) { grid-column: span 2; }

#invPage .control-label {
    float: none;
    width: auto;
    padding: 0;
    text-align: left;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--ink2);
    text-transform: uppercase;
    letter-spacing: .3px;
    margin: 0;
}

#invPage .asteric { color: var(--red); }

#invPage .form-group > div[class^="col-"],
#invPage .form-group > div[class*=" col-"] {
    float: none;
    width: 100%;
    padding: 0;
}

/* .form-control reads as .sdx-in */
#invPage .form-control {
    height: 40px;
    border: 1.4px solid var(--line);
    border-radius: var(--rs);
    padding: 0 12px;
    font-size: 13.5px;
    background: #fff;
    color: var(--ink) !important;
    width: 100%;
    box-shadow: none;
    transition: border-color .15s, box-shadow .15s;
}

#invPage .form-control:focus {
    outline: none;
    border-color: var(--g);
    box-shadow: 0 0 0 3px var(--gsoft);
}

#invPage textarea.form-control {
    height: auto;
    min-height: 84px;
    padding: 10px 12px;
    resize: vertical;
}

#invPage select.form-control {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 34px;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%237b8a82' d='M1.4 0L6 4.6 10.6 0 12 1.4 6 7.4 0 1.4z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    cursor: pointer;
}

/* Select2 given the same shape, v3 and v4 markup alike. */
#invPage .select2-container { width: 100% !important; }

#invPage .select2-container .select2-choice,
#invPage .select2-container--default .select2-selection--single {
    height: 40px !important;
    line-height: 38px;
    border: 1.4px solid var(--line) !important;
    border-radius: var(--rs) !important;
    background: #fff !important;
    padding-left: 12px;
    color: var(--ink);
}

#invPage .select2-container .select2-choice > .select2-chosen,
#invPage .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    color: var(--ink);
    padding-left: 0;
    font-size: 13.5px;
}

#invPage .select2-container .select2-choice .select2-arrow,
#invPage .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px;
    border: 0;
    background: none;
}

/* The soft "already used" notice — the module's own .sdx-note, amber,
   because it is a warning and never stops the entry saving. */
#invPage .batch-no-warning {
    background: var(--amberS);
    border: 1px solid #f0e0bd;
    color: var(--amber);
    border-radius: var(--rs);
    padding: 9px 13px;
    font-size: 12.5px;
    line-height: 1.5;
    margin: 2px 0 0;
}

#invPage .batch-no-warning .fa { margin-right: 6px; }

#invPage .form-control.batch-no-used {
    border-color: #e4c98a;
    background-color: #fffdf7;
}

/* "Allowed For" — the two usage checkboxes sit on one line and read as
   controls, not as a label stack. */
#invPage .inv-checks {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    min-height: 40px;
}

#invPage .inv-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    padding: 0 13px;
    height: 40px;
    border: 1.4px solid var(--line);
    border-radius: var(--rs);
    background: #fff;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink2);
    text-transform: none;
    letter-spacing: 0;
    cursor: pointer;
    transition: border-color .15s, background-color .15s, color .15s;
}

#invPage .inv-check input[type="checkbox"] {
    margin: 0;
    width: 15px;
    height: 15px;
    accent-color: var(--g);
    cursor: pointer;
}

/* Ticked reads as on, so the desk can see at a glance what the batch is
   allowed for without reading each box. */
#invPage .inv-check.on {
    background: var(--gsoft2);
    border-color: #cfe6d8;
    color: var(--gd);
}

#invPage .inv-hint {
    font-size: 11px;
    color: var(--muted);
    margin-top: 6px;
}

/* The AJAX error holders, read as .sdx-err does. */
#invPage h4[id^="Inventory-"],
#invPage h4[id^="Product-"] {
    text-align: left !important;
    font-size: 12px;
    font-weight: 500;
    color: var(--red) !important;
    margin: 2px 0 0;
    padding: 0;
    line-height: 1.5;
}

@media (max-width: 1100px) {
    #invPage .form-body { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 767px) {
    #invPage .form-body { grid-template-columns: 1fr; }
}
</style>
<script type="text/javascript">
    /* The type is pre-selected, so load its section by itself, exactly as a
       manual change would. This waits for window load, not document ready:
       the layout installs the CSRF token on $.ajaxSetup in its own ready
       handler, and firing before that would get the request rejected (419). */
    $(window).on('load', function(){
        var $type = $('[name=type]');
        if($type.val() && $.trim($('#AppendIncomingTypeDetails').html()) === ''){
            $type.trigger('change');
        }
    });

    /* HIDDEN FOR NOW - DO NOT DELETE.
       This printed the product's type ("Greenwave Textile Products" / trader
       product) under the Product dropdown and loaded that product's packing
       labels. The client asked for the text to go, and the label dropdown it
       fed lives inside the packing block that is switched off in
       append-material-details.blade.php, so the request had nothing left to
       fill either.

       TO BRING IT BACK: uncomment this handler and the <span
       id="AppendProductType"> in append-material-details.blade.php.

    $(document).on('change','[name=product_id]',function(){
        var productId = $(this).val();
        var type = $(this).find(':selected').attr('data-product_type');
        if(type===""){
            $('#AppendProductType').html('');
        }else{
            $('#AppendProductType').html(type);
            $.ajax({
                data : {product_id: productId},
                type : 'GET',
                url : '/admin/inventory/get-product-labels',
                success:function(resp){
                    $('[name=label_id]').html(resp);
                },
                error:function(){

                }
            })
        }
    })
    */

    /* "Allowed For" — a ticked box reads as on. */
    $(document).on('change', '.inv-check input[type="checkbox"]', function(){
        $(this).closest('.inv-check').toggleClass('on', this.checked);
    });

    $(document).on('inv:rendered', function(){
        $('.inv-check').each(function(){
            $(this).toggleClass('on', $(this).find('input[type="checkbox"]').is(':checked'));
        });
    });

    /* Batch No. already on the books?
       A warning only - the entry saves either way, which is why nothing here
       touches the submit button or the error holders. */
    var batchCheckTimer = null;
    var batchCheckXhr   = null;

    $(document).on('input','[name=supplier_batch_no]',function(){
        var $input  = $(this);
        var $notice = $('#BatchNoWarning');
        var value   = $.trim($input.val());

        clearTimeout(batchCheckTimer);
        if(batchCheckXhr){ batchCheckXhr.abort(); }

        $input.removeClass('batch-no-used');

        if(value === ''){
            $notice.stop(true, true).hide().html('');
            return;
        }

        // Wait for a pause in typing so a long batch number is one request,
        // not one per keystroke.
        batchCheckTimer = setTimeout(function(){
            batchCheckXhr = $.ajax({
                url  : '/admin/inventory/check-batch-no',
                type : 'GET',
                data : { supplier_batch_no: value, type: $('[name=type]').val() },
                success: function(resp){
                    // The field may have moved on while the request was out.
                    if($.trim($input.val()) !== value){ return; }

                    if(resp && resp.used){
                        $input.addClass('batch-no-used');
                        $notice.html('<i class="fa fa-exclamation-triangle"></i> ' + resp.message).stop(true, true).fadeIn(150);
                    }else{
                        $input.removeClass('batch-no-used');
                        $notice.stop(true, true).hide().html('');
                    }
                },
                error: function(){
                    // A failed check must never get in the way of saving.
                    $notice.hide().html('');
                }
            });
        }, 350);
    })

    $(document).on('change','[name=type]',function(){
        var type = $(this).val();
        if(type==""){
            $('#AppendIncomingTypeDetails').html('');
        }else{
            $('.loadingDiv').show();
            $.ajax({
                url : '/admin/append-material-details',
                data : {type :type},
                type : 'POST',
                success:function(resp){
                    $('#AppendIncomingTypeDetails').html(resp.view);
                    refreshSelect2();
                    $(document).trigger('inv:rendered');
                    $('.loadingDiv').hide();
                },
                error:function(){
                }
            })
        }
    })

    $("#InventoryForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = new FormData(this);
        $.ajax({
            url: '/admin/add-incoming-material',
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Inventory-'+i).addClass('error-triggered');
                        $('#Inventory-'+i).attr('style', '');
                        $('#Inventory-'+i).html(error);
                        setTimeout(function () {
                            $('#Inventory-'+i).css({
                                'display': 'none'
                            });
                        $('#Inventory-'+i).removeClass('error-triggered');
                        }, 5000);
                    });
                    $('html,body').animate({
                        scrollTop: $('.error-triggered').first().stop().offset().top - 200
                    }, 1000);
                }else{
                    window.location.href= data.url;
                }
            }
        });
    });
    $(document).on("change",'.finalPackingType',function(){ 
        var packingType = $(this).find(':selected').text();  
        var tareWeight =  $(this).find(':selected').data('tare_weight');
        var stock =  $(this).find(':selected').data('stock');
        if(tareWeight >= 0){
            $(this).closest('tr').children('td:eq(0)').find('p').html('<small>(Tare Weight: '+tareWeight+'kg) <br>'+'(Available Stock: '+stock+')</small>');
            $(this).closest('tr').children('td:eq(2)').children().val('');
            $(this).closest('tr').children('td:eq(3)').find('p').html('');
            $(this).closest('tr').children('td:eq(2)').find('p').html('');
        }else{
            $(this).closest('tr').children('td:eq(0)').find('p').html('');

        }
        $(this).closest('tr').children('td:eq(4)').find('p').html(packingType);
        //alert(tareWeight);
    });

    $(document).on("keyup",'.finalNetFillSize',function(){  
        var netfillsize = $(this).val();
        var noofpacks = $(this).closest('tr').children('td:eq(1)').children().val();
        var packingtype = $(this).closest('tr').children('td:eq(0)').children().val();
        var tareWeight = $(this).closest('tr').children('td:eq(0)').children().find(':selected').data('tare_weight');
        if(packingtype ==""){
            $(this).val('');
            alert('Please select packing type');
            return false;
        }else if(noofpacks <=0){
            $(this).val('');
            alert('Please enter No of packs');
            return false;
        }
        var materialFilled = noofpacks * netfillsize;
        var totalnetfillsize =  (parseFloat(tareWeight)+parseFloat(netfillsize)).toFixed(2);

        $(this).closest('tr').children('td:eq(2)').find('p').html('('+totalnetfillsize+'kg)');

        $(this).closest('tr').children('td:eq(3)').find('p').html(materialFilled+' kg');
        
    });
    
    $(document).on("keyup",'.finalNoOfPacks',function(){  
        $(this).closest('tr').children('td:eq(2)').children().val('');
        $(this).closest('tr').children('td:eq(3)').find('p').html('');
    });
</script>
<script type="text/javascript">
    window.history.pushState("", "", "/admin/add-incoming-material");
</script>
@endsection