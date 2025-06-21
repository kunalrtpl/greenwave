<html>
   <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Greenwave | Enquiry Form</title>
      <link rel="stylesheet" href="{{asset('css/backend_css/bootstrap/css/bootstrap.min.css')}}" />
      <style type="text/css">
         * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         -webkit-box-sizing: border-box;
         -moz-box-sizing: border-box;
         -webkit-font-smoothing: antialiased;
         -moz-font-smoothing: antialiased;
         -o-font-smoothing: antialiased;
         font-smoothing: antialiased;
         text-rendering: optimizeLegibility;
         }
         body {
         font-family: "Roboto", Helvetica, Arial, sans-serif;
         font-weight: 100;
         font-size: 12px;
         line-height: 30px;
         color: #777;
         /*background: #e4e5e6;*/
         }
         .container {
         max-width: 340px;
         width: 100%;
         margin: 0 auto;
         position: relative;
         padding:20px 10px;
         }
         .contact input[type="text"],
         .contact input[type="email"],
         .contact input[type="tel"],
         .contact input[type="url"],
         .contact textarea,
         .contact button[type="submit"] {
         font: 400 12px/16px "Roboto", Helvetica, Arial, sans-serif;
         }
         .alert-success
         {
            line-height:22px;
            padding-top: 5px;
            padding-bottom: 7px;
         }
         .contact {
         /*background: #F9F9F9;*/
         /*padding: 18px;*/
         margin: 50px 0;
         /*box-shadow: 0 0 1px 0 rgba(0, 0, 0, 0.2), 0 1px 3px 0 rgba(0, 0, 0, 0.24);*/
         }
         .contact h3 {
         display: block;
         font-size: 12px;
         font-weight: 400;
         margin-bottom:6px;
         color:#000;
         }
         .contact h4 {
         margin: 5px 0 15px;
         display: block;
         font-size: 13px;
         font-weight: 400;
         }
         fieldset {
         border: medium none !important;
         min-width: 100%;
         padding: 0;
         width: 100%;
         }
         label
         {
         font-weight: normal;
         font-size: 13px;
         color:#000;
         }
         .tabs h3
         {
            margin:0;
         }
         .contact input[type="text"],
         .contact input[type="email"],
         .contact input[type="tel"],
         .contact input[type="url"],
         .contact textarea {
         width: 100%;
         border: 1px solid #ccc;
         background: #FFF;
         margin: 0 0 5px;
         padding: 10px;
         }
         .contact textarea
         {
            padding:20px;
         }
         .contact input[type="text"]:hover,
         .contact input[type="email"]:hover,
         .contact input[type="tel"]:hover,
         .contact input[type="url"]:hover,
         .contact textarea:hover {
         -webkit-transition: border-color 0.3s ease-in-out;p:;
         -moz-transition: border-color 0.3s ease-in-out;
         transition: border-color 0.3s ease-in-out;
         border: 1px solid #aaa;
         }
         .contact textarea {
         /*height: 100px;*/
         max-width: 100%;
         resize: none;
         }
         .contact button[type="submit"] {
         cursor: pointer;
         width: 100%;
         border: none;
         background: #3e6522;
         color: #FFF;
         margin: 0 0 5px;
         padding: 10px;
         font-size: 15px;
         margin-top:20px;
         }
         .contact button[type="submit"]:hover {
         background: #3e6522;
         -webkit-transition: background 0.3s ease-in-out;
         -moz-transition: background 0.3s ease-in-out;
         transition: background-color 0.3s ease-in-out;
         }
         .contact button[type="submit"]:active {
         box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.5);
         }
         .copyright {
         text-align: center;
         }
         .contact input:focus,
         .contact textarea:focus {
         outline: 0;
         border: 1px solid #aaa;
         }
         ::-webkit-input-placeholder {
         color: #888;
         }
         :-moz-placeholder {
         color: #888;
         }
         ::-moz-placeholder {
         color: #888;
         }
         :-ms-input-placeholder {
         color: #888;
         }
         .contact
         {
         margin:0;
         }
         [data-tab-content] {
         display: none;
         }
         .active[data-tab-content] {
         display: block;
         }
         body {
         padding: 0;
         margin: 0;
         }
         .tabs {
         display: flex;
         justify-content: space-around;
         list-style-type: none;
         margin: 0;
         padding: 0;
         /*border-bottom: 1px solid black;*/
         }
         .tab {
         cursor: pointer;
         padding: 10px;
         background-color: #e6e6e6;
         }
         .tab.active 
         {
          background-color: #3e6522;
          color: #fff;
         }
         .tab:hover {
         background-color:#3e6522;
         }
         .tab-content 
         {
           margin-left: 6px;
           margin-right: 0px;
           margin-top: 19px;
         }
         .tabs li h3
         {
            line-height:normal;
            font-size:12px;
         }
      </style>
   </head>
   <body>
      
      <div class="container">
         @if(Session::has('flash_message_success'))
          <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"></button>{!! session('flash_message_success') !!} </div>
      @endif
         <ul class="tabs">
         <li data-tab-target="#tab1" class="active tab"><h3>Quick Enquiry</h3></li>
         <li data-tab-target="#tab2" class="tab"><h3>Dealership Enquiry</h3></li>
         <li data-tab-target="#tab3" class="tab"><h3>Job Enquiry</h3></li>
      </ul>
      <div class="tab-content">
         <div id="tab1" data-tab-content class="active">
            <form id="QuickEnquiryForm" class="contact" action="" method="post">@csrf
            <fieldset>
               <label>Name</label>
               <input  type="text" tabindex="1" name="name" autofocus>
               <h4 class="text-center text-danger pt-3" style="display: none;" id="QuickEnquiry-name"></h4>
            </fieldset>
            <fieldset>
               <label>Email Address</label>
               <input type="email" name="email" tabindex="2">
               <h4 class="text-center text-danger pt-3" style="display: none;" id="QuickEnquiry-email"></h4>
            </fieldset>
            <fieldset>
               <label>Mobile Number</label>
               <input type="tel" name="mobile" tabindex="3">
               <h4 class="text-center text-danger pt-3" style="display: none;" id="QuickEnquiry-mobile"></h4>
            </fieldset>
            <fieldset>
               <label>Message</label>
               <textarea tabindex="3" name="message"></textarea>
               <h4 class="text-center text-danger pt-3" style="display: none;" id="QuickEnquiry-message"></h4>
            </fieldset>
            <fieldset>
               <button  type="submit" id="contact-submit" data-submit="...Sending">Submit</button>
            </fieldset>
         </form>
         </div>

         <!-- Dealership Enquiry Form start here -->
         <div id="tab2" data-tab-content>
            <form id="DealershipEnquiryForm" class="contact" action="" method="post">@csrf
            <fieldset>
               <label>Business Name</label>
               <input  type="text" name="business_name" autofocus>
               <h4 class="text-center text-danger pt-3" style="display: none;" id="DealershipEnquiry-business_name"></h4>
            </fieldset>
            <fieldset>
               <label>City</label>
               <input type="text" name="city" >
               <h4 class="text-center text-danger pt-3" style="display: none;" id="DealershipEnquiry-city"></h4>
            </fieldset>
            <fieldset>
               <label>Contact Person</label>
               <input type="text" name="contact_person">
               <h4 class="text-center text-danger pt-3" style="display: none;" id="DealershipEnquiry-contact_person"></h4>
            </fieldset>
            <fieldset>
               <label>Mobile No.</label>
               <input type="text" name="mobile" >
               <h4 class="text-center text-danger pt-3" style="display: none;" id="DealershipEnquiry-mobile"></h4>
            </fieldset>
            <fieldset>
               <label>E-mail</label>
               <input type="text" name="email" >
               <h4 class="text-center text-danger pt-3" style="display: none;" id="DealershipEnquiry-email"></h4>
            </fieldset>
            <fieldset>
               <label>Message (if any)</label>
               <textarea name="message"></textarea>
            </fieldset>
            <fieldset>
               <button name="submit" type="submit" id="contact-submit" data-submit="...Sending">Submit</button>
            </fieldset>
         </form>
         </div>
         <!-- Dealership Enquiry Form end here -->

         <!-- Job Enquiry Form start here -->
         <div id="tab3" data-tab-content>
            <form id="JobEnquiryForm" class="contact" action="" method="post">@csrf
            <fieldset>
               <label>Name</label>
               <input  type="text" name="name"  autofocus>
               <h4 class="text-center text-danger pt-3" style="display: none;" id="JobEnquiry-email"></h4>
            </fieldset>
            <fieldset>
               <label>Mobile No.</label>
               <input type="text" name="mobile">
               <h4 class="text-center text-danger pt-3" style="display: none;" id="JobEnquiry-mobile"></h4>
            </fieldset>
            <fieldset>
               <label>E-mail</label>
               <input type="email" name="email" >
               <h4 class="text-center text-danger pt-3" style="display: none;" id="JobEnquiry-email">
            </fieldset>
            <fieldset>
               <label>Currently Working With Designation</label>
               <input type="text"  name="currently_working">
               <h4 class="text-center text-danger pt-3" style="display: none;" id="JobEnquiry-currently_working">
            </fieldset>
            <fieldset>
               <label>Placed at</label>
               <input type="text" name="placed_at" >
               <h4 class="text-center text-danger pt-3" style="display: none;" id="JobEnquiry-placed_at">
            </fieldset>
            <fieldset>
               <label>Message (if any)</label>
               <textarea  name="message"></textarea>
            </fieldset>
            <fieldset>
               <button name="submit" type="submit" id="contact-submit" data-submit="...Sending">Submit</button>
            </fieldset>
         </form>
         </div>
         <!-- Job Enquiry Form end here -->
      </div>
         
      </div>
      
      <script>
         const tabs = document.querySelectorAll('[data-tab-target]')
         const tabContents = document.querySelectorAll('[data-tab-content]')
         tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                  const target = document.querySelector(tab.dataset.tabTarget)
                  tabContents.forEach(tabContent => {
                  tabContent.classList.remove('active')
               })

               tabs.forEach(tab => {
                  tab.classList.remove('active')
               })

               tab.classList.add('active')
               target.classList.add('active')
            })
         })
      </script>
      <script src="{{asset('js/backend_js/jquery.min.js')}}" type="text/javascript"></script>
      <script type="text/javascript">
      $("#QuickEnquiryForm").submit(function(e){
         $('.loadingDiv').show();
         e.preventDefault();
         var formdata = new FormData(this);
         $.ajax({
            url: '/save-quick-enquiry',
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
               $('.loadingDiv').hide();
               if(!data.status){
                     $.each(data.errors, function (i, error) {
                        $('#QuickEnquiry-'+i).addClass('error-triggered');
                        $('#QuickEnquiry-'+i).attr('style', '');
                        $('#QuickEnquiry-'+i).html(error);
                        setTimeout(function () {
                            $('#QuickEnquiry-'+i).css({
                                'display': 'none'
                            });
                        $('#QuickEnquiry-'+i).removeClass('error-triggered');
                        }, 5000);
                     });
               }else{
                    window.location.href = data.url;
               }
            }
         });
      });
      $("#DealershipEnquiryForm").submit(function(e){
         $('.loadingDiv').show();
         e.preventDefault();
         var formdata = new FormData(this);
         $.ajax({
            url: '/save-dealership-enquiry',
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
               $('.loadingDiv').hide();
               if(!data.status){
                     $.each(data.errors, function (i, error) {
                        $('#DealershipEnquiry-'+i).addClass('error-triggered');
                        $('#DealershipEnquiry-'+i).attr('style', '');
                        $('#DealershipEnquiry-'+i).html(error);
                        setTimeout(function () {
                            $('#DealershipEnquiry-'+i).css({
                                'display': 'none'
                            });
                        $('#DealershipEnquiry-'+i).removeClass('error-triggered');
                        }, 5000);
                     });
               }else{
                    window.location.href = data.url;
               }
            }
         });
      });
      $("#JobEnquiryForm").submit(function(e){
         $('.loadingDiv').show();
         e.preventDefault();
         var formdata = new FormData(this);
         $.ajax({
            url: '/save-job-enquiry',
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
               $('.loadingDiv').hide();
               if(!data.status){
                     $.each(data.errors, function (i, error) {
                        $('#JobEnquiry-'+i).addClass('error-triggered');
                        $('#JobEnquiry-'+i).attr('style', '');
                        $('#JobEnquiry-'+i).html(error);
                        setTimeout(function () {
                            $('#JobEnquiry-'+i).css({
                                'display': 'none'
                            });
                        $('#JobEnquiry-'+i).removeClass('error-triggered');
                        }, 5000);
                     });
               }else{
                    window.location.href = data.url;
               }
            }
         });
      });
   </script>
   </body>
</html>