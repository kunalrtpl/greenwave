jQuery(document).ready(function() { 
    $.ajaxSetup({
        headers:
        {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });    
    TableAjax.init();
    $(document).on('click','.toogle_switch',function(){
        if($(this).hasClass('bootstrap-switch-on')){
            $(this).removeClass('bootstrap-switch-on');
            $(this).addClass('bootstrap-switch-off');
            var status=0;
            var id_sent=$(this).attr('id');
        }
        else{
            $(this).removeClass('bootstrap-switch-off');
            $(this).addClass('bootstrap-switch-on');
            var status=1;
            var id_sent=$(this).attr('id');
        }
        var table = $(this).attr('rel');
        var ajax_url='status';
        $.ajax({
            url:ajax_url,
            type:'POST',
            data:{
                'id':id_sent,'status':status, 'table':table
            },
            success:function(msg) {
            }
        })
    });

    $('#change_pass').formValidation({
        framework: 'bootstrap',
        message: 'This value is not valid',
        icon:{
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        err:{
            container: 'popover'
        },
        fields:{
            "password":{
                validators:{
                    notEmpty:{
                        message: 'Current password is required'
                    },
                    remote:{
                        message: 'Current password is incorrect',
                        url: '/admin/checkAdminPassword',
                        type: 'POST',
                        delay: 1000     // Send Ajax request every 2 seconds
                    }
                }
            },
            "new_password":{
                validators:{
                    notEmpty:{
                        message: 'New password is required'
                    }
                }
            },
            "re_password":{
                validators:{
                    notEmpty:{
                        message: 'Confirm Password  is required'
                    },
                    identical:{
                        field: "new_password",
                        message: 'Confirm Password is not match with New Password'
                    }
                }
            }
        }
    });

    $('#addEditUser').formValidation({
        framework: 'bootstrap',
        message: 'This value is not valid',
        icon:{
            /*valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',*/
            validating: 'glyphicon glyphicon-refresh'
        },
        err:{
            container: 'popover'
        },
        fields:{
            "first_name":{
                validators:{
                    notEmpty:{
                        message: 'First Name is required'
                    },
                }
            },
            "last_name":{
                validators:{
                    notEmpty:{
                        message: 'Last Name is required'
                    },
                }
            },
            "company_name":{
                validators:{
                    notEmpty:{
                        message: 'Company Name is required'
                    },
                }
            },
            "email":{
                validators:{
                    notEmpty:{
                        message: 'Email is required.'
                    },
                    emailAddress:{
                        message: 'This Email is not a valid email address'
                    },
                    remote:{
                        message: 'This email already exists.',
                        url: '/admin/CheckUserEmail',
                        type: 'POST',
                        delay: 2000     // Send Ajax request every 2 seconds
                    }
                }
            },
            "password":{
                validators:{
                    notEmpty:{
                        message: 'Password  is required'
                    },
                    stringLength:{
                        min: 6,
                        max: 30,
                        message: 'The password must be more than 5 characters.'
                    },
                }
            }
        }
    });

    $('#addEditRider').formValidation({
        framework: 'bootstrap',
        message: 'This value is not valid',
        icon:{
            /*valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',*/
            validating: 'glyphicon glyphicon-refresh'
        },
        err:{
            container: 'popover'
        },
        fields:{
            "name":{
                validators:{
                    notEmpty:{
                        message: 'Name is required'
                    },
                }
            },
            "state":{
                validators:{
                    notEmpty:{
                        message: 'State is required'
                    },
                }
            },
            "city":{
                validators:{
                    notEmpty:{
                        message: 'City is required'
                    },
                }
            },
            "email":{
                validators:{
                    notEmpty:{
                        message: 'Email is required.'
                    },
                    emailAddress:{
                        message: 'This Email is not a valid email address'
                    },
                    remote:{
                        message: 'This email already exists.',
                        url: '/admin/checkRiderEmail',
                        type: 'POST',
                        delay: 2000     // Send Ajax request every 2 seconds
                    }
                }
            },
            "phone":{
                validators:{   
                    notEmpty:{
                        message: 'Mobile Number is required.'
                    },
                    stringLength:{
                        min: 9,
                        max: 9,
                        message: 'Phone must be between 9 digits only'
                    },
                    remote:{
                        message: 'This phone already exists.',
                        url: '/admin/checkRiderPhone',
                        type: 'POST',
                        delay: 2000     // Send Ajax request every 2 seconds
                    }
                }
            },
            "password":{
                validators:{
                    notEmpty:{
                        message: 'Password  is required'
                    },
                    stringLength:{
                        min: 6,
                        max: 30,
                        message: 'The password must be more than 5 letters.'
                    },
                }
            }
        }
    });
    

    $(document).on('change','.getCountry',function(){
        var countryid = $(this).val(); 
        $("#AppendCities").html('<option value="">Select</option');
        $.ajax({
            url : '/get-states',
            data : {countryid: countryid},
            type : 'post',
            success:function(resp){
                $("#AppendStates").html(resp);
            },
            error:function(){}
        })
    });

    $(document).on('change','.getState',function(){
        var stateid = $(this).val(); 
        $.ajax({
            url : '/get-cities',
            data : {stateid: stateid},
            type : 'post',
            success:function(resp){
                $("#AppendCities").html(resp);
            },
            error:function(){}
        })
    });

    //Product Validation Starts
    $('#addEditProduct').formValidation({
        framework: 'bootstrap',
        excluded: [':disabled'],
        message: 'This value is not valid',
        icon:{
            /*valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',*/
            validating: 'glyphicon glyphicon-refresh'
        },
        err:{
            container: 'popover'
        },
        fields:{
            "price":{
                validators:{   
                    notEmpty: {
                        message: 'This fields is required.'
                    },
                    regexp: {
                        regexp: /^[1-9]\d*(\.\d+)?$/,
                        message: 'Price can only accept decimal and numeric values'
                    }
                }
            },
            "volume":{
                validators:{   
                    notEmpty:{
                        message: 'This field is required.'
                    }
                }
            },
            "carton_bottles":{
                validators:{   
                    notEmpty:{
                        message: 'This field is required.'
                    }
                }
            },
            "sort":{
                validators:{   
                    notEmpty:{
                        message: 'This field is required.'
                    }
                }
            },
        }
    })
    .on('err.field.fv', function(e, data) {
        data.fv.disableSubmitButtons(false);
    })
    .on('success.field.fv', function(e, data) {
        data.fv.disableSubmitButtons(false);
    })/*
    .find('[name="product_description"]')
    .each(function() {
        $(this).ckeditor().editor
    });*/

    // Coupon Validation Starts
    $('#addCouponForm').formValidation({
        framework: 'bootstrap',
        excluded: [':disabled'],
        message: 'This value is not valid',
        icon: 
        {
            /*valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',*/
            validating: 'glyphicon glyphicon-refresh'
        },
        err: 
        {
            container: 'popover'
        },
        fields:
        {
            "code": 
            {
                validators: 
                {   
                    remote: 
                    {
                        message: 'This coupon code already exists.',
                        url: '/admin/checkCouponCode',
                        type: 'POST',
                        delay: 2000     // Send Ajax request every 2 seconds
                    }
                }
            },
            "expiry_date": 
            {
                validators: 
                {
                    notEmpty: 
                    {
                        message: 'Expiry date is required.'
                    }, 
                    date: {
                        format: 'YYYY-MM-DD',
                        message: 'The date is not a valid'
                    }
                }
            },
            "amount": 
            {
                validators: 
                {
                    notEmpty: 
                    {
                        message: 'Coupon Discount is required.'
                    },
                    regexp:{
                        regexp : '^(0?[1-9]|[1-9][0-9])$',
                        message: 'Discount must be between 1 to 99'
                    }
                }
            },
        }
    });

    $("#Manual").click(function(){
        $("#textField").show();
    });

    $("#Automatic").click(function(){
        $("#textField").hide();
        $('#addCouponForm').formValidation('removeField','code');
    });

    /*SubAdmin Roles Scripts starts*/
    $(document).on('change','.getModuleid',function(){
        var roleType = $(this).attr('data-attr');
        var id = $(this).attr('rel');
        if(roleType === "View"){
            $('#edit-'+id).prop('checked',false);
            $('#delete-'+id).prop('checked',false);
        }else if(roleType==="Edit"){
            $('#view-'+id).prop('checked',true);
            $('#delete-'+id).prop('checked',false);
        }else if(roleType==="Delete"){
            $('#view-'+id).prop('checked',true);
            $('#edit-'+id).prop('checked',true);
        }
    });
    /*SubAdmin Roles Scripts ends*/
    
    //SubAdmin Validations
    $('#addEditSubadmin').formValidation({
        framework: 'bootstrap',
        excluded: [':disabled'],
        message: 'This value is not valid',
        icon:{
            /*valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',*/
            validating: 'glyphicon glyphicon-refresh'
        },
        err:{
            container: 'popover'
        },
        fields:{
            "name":{
                validators:{
                    notEmpty:{
                        message: 'This field is required'
                    },
                }
            },
            "username":{
                validators:{
                    notEmpty:{
                        message: 'This field is required'
                    },
                    remote:{
                        message: 'This username already exists.',
                        url: '/admin/checkAdminUsername',
                        type: 'POST',
                        delay: 2000     // Send Ajax request every 2 seconds
                    }
                }
            },
            "email":{
                validators:{
                    notEmpty:{
                        message: 'This field is required'
                    },
                    emailAddress:{
                        message: 'This Email is not a valid email address'
                    },
                }
            },
            "password":{
                validators:{
                    notEmpty:{
                        message: 'This field is required'
                    },
                    stringLength:{
                        min: 6,
                        max: 10,
                        message: 'The password must be more than 5 letters.'
                    },
                }
            },
        }
    })
    .on('err.field.fv', function(e, data) {
        data.fv.disableSubmitButtons(false);
    })
    .on('success.field.fv', function(e, data) {
        data.fv.disableSubmitButtons(false);
    });


    $('#brand').formValidation({
        framework: 'bootstrap',
        excluded: ':disabled',
        message: 'This value is not valid',
        icon: 
        {
            /*valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',*/
            validating: 'glyphicon glyphicon-refresh'
        },
        err: 
        {
            container: 'popover'
        },
        fields: 
        {
          "name": 
            {
                validators: 
                {
                    notEmpty: 
                    {
                        message: 'This field is required'
                    },
                }
            },
        }
    });

    $('#add').on('hidden.bs.modal', function(){
        $('#brand').formValidation('resetForm', true);
    });

    $('.datePicker')
        .datepicker({
        format: 'yyyy-mm-dd'/*,
        startDate: new Date()*/
    }).on('changeDate', function(e) {
        $(this).datepicker('hide');
    });

});

function ConfirmDelete() {
      if(confirm('Are you sure?')){
         e.preventDefault();
         return true;
      }
      return false;
   }