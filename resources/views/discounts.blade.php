<html>
   <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Greenwave Page</title>
      <style type="text/css">
         * {
         margin: 0;
         padding: 0;
         text-indent: 0;
         }
         .content-area {
         padding: 40px 50px;
         }
         h1 {
         color: black;
         font-family: Calibri, sans-serif;
         font-style: normal;
         font-weight: bold;
         text-decoration: none;
         font-size: 14pt;
         }
         .s1 {
         color: black;
         font-family: "Times New Roman", serif;
         font-style: normal;
         font-weight: normal;
         text-decoration: none;
         font-size: 12pt;
         }
         .h2 {
         color: black;
         font-family: Calibri, sans-serif;
         font-style: normal;
         font-weight: 500;
         text-decoration: none;
         font-size: 12pt;
         width: 90%;
         display: block;
         padding: 5px;
         }
         .s2 {
         color: black;
         font-family: Calibri, sans-serif;
         font-style: normal;
         font-weight: normal;
         text-decoration: none;
         font-size: 12pt;
         }
         .s3 {
         color: black;
         font-family: Calibri, sans-serif;
         font-style: normal;
         font-weight: bold;
         text-decoration: none;
         font-size: 12pt;
         padding-left: 2pt;
         text-indent: 0pt;
         line-height:13pt;
         text-align: left;
         }
         ul li:before 
         {
         content: "";
         width: 5px;
         height: 5px;
         line-height: 8px;
         display: -webkit-inline-box;
         margin-left: -9px;
         background-color: black;
         border-radius: 50%;
         }
         ul li
         {
         margin-left:20px;
         margin-bottom:2px;
         }
         .s4 {
         color: black;
         font-family: Calibri, sans-serif;
         font-style: normal;
         font-weight: normal;
         text-decoration: none;
         font-size: 12pt;
         }
         p {
         color: black;
         font-family: Calibri, sans-serif;
         font-style: normal;
         font-weight: normal;
         text-decoration: none;
         font-size: 10pt;
         margin: 0pt;
         }
         .s5 {
         color: #0070c0;
         font-family: Calibri, sans-serif;
         font-style: italic;
         font-weight: normal;
         text-decoration: none;
         font-size: 10pt;
         margin-left:20px;
         margin-top:10px;
         }
         li {
         display: block;
         }
         #l1 {
         padding-left: 0pt;
         }
         #l1 > li > *:first-child:before {
         content: "* ";
         color: black;
         font-family: Calibri, sans-serif;
         font-style: normal;
         font-weight: normal;
         text-decoration: none;
         font-size: 10pt;
         }
         table,
         tbody {
         vertical-align: top;
         overflow: visible;
         }
         .main-title {
         padding-top: 3pt;
         padding-left: 8pt;
         text-indent: 0pt;
         text-align: left;
         }
         .heading {
         /*padding-top: 5pt;*/
         padding-left: 6pt;
         text-indent: 0pt;
         text-align: left;
         }
         .sub-title {
          padding-top: 5pt;
         padding-left: 8pt;
         text-indent: 0pt;
         text-align: left;
         }
         .table-data {
         border-collapse: collapse;
         margin-left: 5.5pt;
         }
         .payment-term {
         width: 162pt;
         border-top-style: solid;
         border-top-width: 1pt;
         border-left-style: solid;
         border-left-width: 1pt;
         border-bottom-style: solid;
         border-bottom-width: 1pt;
         border-right-style: solid;
         border-right-width: 1pt;
         padding:5px;
         }
         .discount-data {
         width: 90pt;
         border-top-style: solid;
         border-top-width: 1pt;
         border-left-style: solid;
         border-left-width: 1pt;
         border-bottom-style: solid;
         border-bottom-width: 1pt;
         border-right-style: solid;
         border-right-width: 1pt;
         padding:5px;
         }
         .d-data {
         padding-left: 2pt;
         text-indent: 0pt;
         line-height: 13pt;
         text-align: left;
         }
         .term-text {
         padding-left: 8pt;
         text-indent: 0pt;
         line-height: 114%;
         text-align: left;
         }
         .italic-text {
         padding-left: 8pt;
         text-indent: 0pt;
         text-align: left;
         }
         .accordion button {
         position: relative;
         display: block;
         text-align: left;
         width: 100%;
         padding: 5px;
         color: #000;
         font-size: 1.15rem;
         font-weight: 400;
         border: none;
         background: none;
         outline: none;
         }
         .accordion button:hover,
         .accordion button:focus {
         cursor: pointer;
         color: #000;
         }
         .accordion button:hover::after,
         .accordion button:focus::after {
         cursor: pointer;
         color: #03b5d2;
         border: 1px solid #03b5d2;
         }
         .accordion button .accordion-title {
         padding: 5px;
         background-color: #e9e9e9;
         /*background-color:#d6f19e;*/
         width: 100%;
         display: block;
         }
         .accordion button .icon {
         display: inline-block;
         position: absolute;
         top: 14px;
         right: 15px;
         width: 22px;
         height: 22px;
         border: 1px solid;
         border-radius: 22px;
         }
         .accordion button .icon::before {
         display: block;
         position: absolute;
         content: "";
         top: 10px;
         left: 6px;
         width: 10px;
         height: 2px;
         background: currentColor;
         }
         .accordion button .icon::after {
         display: block;
         position: absolute;
         content: "";
         top: 6px;
         left: 10px;
         width: 2px;
         height: 10px;
         background: currentColor;
         }
         .accordion button[aria-expanded="true"] {
         color: #000;
         }
         .accordion button[aria-expanded="true"] .icon::after {
         width: 0;
         }
         .accordion button[aria-expanded="true"] + .accordion-content {
         opacity: 1;
         max-height: 100%;
         transition: all 200ms linear;
         will-change: opacity, max-height;
         }
         .accordion .accordion-content {
         opacity: 0;
         max-height: 0;
         overflow: hidden;
         transition: opacity 200ms linear, max-height 200ms linear;
         will-change: opacity, max-height;
         }
         /*.accordion .accordion-content p {
         font-size: 1rem;
         font-weight: 300;
         margin:10px;
         }*/
         ul.a {
         list-style-type: circle;
         }
         ul.b {
         list-style-type: square;
         }
         ol.c {
         list-style-type: upper-roman;
         }
         ol.d {
         list-style-type: lower-alpha;
         }
         @media only screen and (max-width: 768px) {
         .content-area {
         padding: 0px 10px;
         }
         }
      </style>
   </head>
   <body>
      <div class="content-area">
         <!-- <h1 class="main-title">Standard Discounts</h1> -->
         <br />
         <div class="accordion">
            <div class="accordion-item">
               <button id="accordion-button-1" @if(isset($_GET['default-open']) && $_GET['default-open'] =="payment") aria-expanded="true" @else aria-expanded="false" @endif>
               <span class="accordion-title">
               <span class="h2">
               Payment Term Discount (PTD)
               </span>
               </span>
               <span class="icon" aria-hidden="true"></span>
               </button>
               <div class="accordion-content">
                  <p class="s2 sub-title">(Early Payments attract Higher Discounts)</p>
                  <br />
                  <table class="table-data" cellspacing="0">
                     <tr style="height: 14pt;">
                        <td class="payment-term" bgcolor="#D0CECE">
                           <p class="s3">Payment Term</p>
                        </td>
                        <td class="discount-data" bgcolor="#D0CECE">
                           <p style="text-align:center;" class="s3">Discount %</p>
                        </td>
                     </tr>
                     <tr style="height: 14pt;">
                        <td class="payment-term">
                           <p class="s4 d-data">90 days</p>
                        </td>
                        <td class="discount-data">
                           <p class="s4 d-data" style="padding-right: 1pt; text-indent: 0pt; line-height: 13pt; text-align: center;">4%</p>
                        </td>
                     </tr>
                     <tr style="height: 14pt;">
                        <td class="payment-term">
                           <p class="s4 d-data">60 days</p>
                        </td>
                        <td class="discount-data">
                           <p style="text-align: center;" class="s4 d-data">8%</p>
                        </td>
                     </tr>
                     <tr style="height: 14pt;">
                        <td class="payment-term">
                           <p class="s4 d-data">30 days</p>
                        </td>
                        <td class="discount-data">
                           <p style="text-align: center;" class="s4 d-data">12%</p>
                        </td>
                     </tr>
                     <tr style="height: 14pt;">
                        <td class="payment-term">
                           <p class="s4 d-data">7 days</p>
                        </td>
                        <td class="discount-data">
                           <p style="text-align: center;" class="s4 d-data">14%</p>
                        </td>
                     </tr>
                  </table>
                  <br />
                  <ul>
                     <li>
                        Applicable on List Price
                     </li>
                     <li>
                        As per the agreed terms between dealer and customer, applicable PTD will be given either at the time of billing or at the time of payment.
                     </li>
                     <li>
                        No discount if payment goes beyond 90 days.
                     </li>
                  </ul>
                  <p style="margin-bottom:10px;" class="s5">Company reserves the right to alter the discount structure any time.</p>
               </div>
            </div>
            <div class="accordion-item">
               <button id="accordion-button-4" @if(isset($_GET['default-open']) && $_GET['default-open'] =="spsod") aria-expanded="true" @else aria-expanded="false" @endif>
               <span class="accordion-title">
               <span class="h2">
               Single Product Single Order Discount (SPSOD)
               </span>
               </span>
               <span class="icon" aria-hidden="true"></span>
               </button>
               <div class="accordion-content">
                  <p></p>
                  <p style="text-indent: 0pt; text-align: left;"><br /></p>
                  <p class="s2 sub-title">(High Value Order of a Single Product makes it More Cost Effective)</p>
                  <p style="text-indent: 0pt; text-align: left;"><br /></p>
                  <table class="table-data" cellspacing="0">
                     <tr style="height: 14pt;">
                        <td class="payment-term" bgcolor="#D0CECE">
                           <p class="s3">Order Value</p>
                        </td>
                        <td class="discount-data" bgcolor="#D0CECE">
                           <p style="text-align: center;" class="s3">Discount %</p>
                        </td>
                     </tr>
                     <tr style="height: 14pt;">
                        <td class="payment-term">
                           <p class="s4 d-data">0-799999</p>
                        </td>
                        <td class="discount-data">
                           <p class="s4 d-data" style="padding-right: 1pt; text-indent: 0pt; line-height: 13pt; text-align: center;">0%</p>
                        </td>
                     </tr>
                     <tr style="height: 14pt;">
                        <td class="payment-term">
                           <p class="s4 d-data">800000 - 1499999</p>
                        </td>
                        <td class="discount-data">
                           <p style="text-align:center;" class="s4 d-data">2%</p>
                        </td>
                     </tr>
                     <tr style="height: 14pt;">
                        <td class="payment-term">
                           <p class="s4 d-data">1500000 & above</p>
                        </td>
                        <td class="discount-data">
                           <p style="text-align: center;" class="s4 d-data">4%</p>
                        </td>
                     </tr>
                  </table>
                  <p style="text-indent: 0pt; text-align: left;"><br /></p>
                  <ul>
                     <li>
                        Applicable on List Price
                     </li>
                     <li>
                        Customer can avail a special discount if he places a High Value Order of a Single Product as per the mentioned slabs.
                     </li>
                     <li>
                        Order Value will be calculated at List Price.
                     </li>
                     <li>
                        All orders are subject to confirmation.
                     </li>
                     <li>
                        The delivery of material can be scheduled for a period of maximum 15 days from the date of order
                     </li>
                     <li>
                        For any reason, if the total order quantity is not picked by the customer, then discount given on already delivered partial quantity will stand reversed.
                     </li>
                  </ul>
                  <p style="text-indent: 0pt; text-align: left;"><br /></p>
                  <p style="margin-bottom:10px;" class="s5">Company reserves the right to alter the discount structure any time.</p>
               </div>
            </div>
            <?php $discounts = \App\Discount::get_monthly_discounts(); 
               //echo "<pre>"; print_r($discounts); die;
               ?>
            <div class="accordion-item">
               <button id="accordion-button-2" @if(isset($_GET['default-open']) && $_GET['default-open'] =="monthly") aria-expanded="true" @else aria-expanded="false" @endif>
               <span class="accordion-title">
               <span class="h2">
               Monthly Net Turnover Discount (MToD)
               </span>
               </span>
               <span class="icon" aria-hidden="true"></span>
               </button>
               <div class="accordion-content">
                  <p class="s2 sub-title">(Higher the Turnover, More the Discount)</p>
                  <br />
                  <table class="table-data" cellspacing="0">
                     <tr style="height: 14pt;">
                        <td class="payment-term" bgcolor="#D0CECE">
                           <p class="s3">Monthly Net Turnover Range</p>
                        </td>
                        <td class="discount-data" bgcolor="#D0CECE">
                           <p style="text-align: center;" class="s3">Discount %</p>
                        </td>
                     </tr>
                     @foreach($discounts as $discount)
                     <tr style="height: 14pt;">
                        <td class="payment-term">
                           <p class="s4 d-data">{{$discount['range_from']}} - {{$discount['range_to']}}</p>
                        </td>
                        <td class="discount-data">
                           <p class="s4 d-data" style="padding-right: 1pt; text-indent: 0pt; line-height: 13pt; text-align: center;">{{$discount['discount']}}%</p>
                        </td>
                     </tr>
                     @endforeach
                  </table>
                  <p style="text-indent: 0pt; text-align: left;"><br /></p>
                  <ul>
                     <li>
                        Applicable on Monthly Sales, net of all discounts
                     </li>
                     <li>
                        Customers with higher monthly sales volume can avail this discount as per the mentioned slabs.
                     </li>
                     <li>
                        If monthly net turnover falls in a particular slab, then respective discount (%) will be applicable on the entire sales starting from Rs. 1/-
                     </li>
                     <li>
                        SPSOD sales will not be eligible for MToD (whereas it will be included for determining the sales slab)
                     </li>
                  </ul>
                  <p class="s5">Company reserves the right to alter the discount structure any time.</p>
               </div>
               <!-- <div class="accordion-item">
                  <button id="accordion-button-3" aria-expanded="false">
                  <span class="accordion-title">
                  <span class="h2">
                  Annual Net Turnover Discount (AToD)
                  </span>
                  </span>
                  <span class="icon" aria-hidden="true"></span>
                  </button>
                  <div class="accordion-content">
                      <p class="s2 sub-title">(Consisitent Turnover throughout the Year is Appreciated)</p>
                      <p style="text-indent: 0pt; text-align: left;"><br /></p>
                      <table class="table-data" cellspacing="0">
                          <tr style="height: 14pt;">
                              <td class="payment-term" bgcolor="#D0CECE">
                                  <p class="s3">Payment Term</p>
                              </td>
                              <td class="discount-data" bgcolor="#D0CECE">
                                  <p style="text-align: right;" class="s3">Discount %</p>
                              </td>
                          </tr>
                          <tr style="height: 14pt;">
                              <td class="payment-term">
                                  <p class="s4 d-data">90 days</p>
                              </td>
                              <td class="discount-data">
                                  <p class="s4 d-data" style="padding-right: 1pt; text-indent: 0pt; line-height: 13pt; text-align: right;">4%</p>
                              </td>
                          </tr>
                          <tr style="height: 14pt;">
                              <td class="payment-term">
                                  <p class="s4 d-data">60 days</p>
                              </td>
                              <td class="discount-data">
                                  <p style="text-align: right;" class="s4 d-data">8%</p>
                              </td>
                          </tr>
                          <tr style="height: 14pt;">
                              <td class="payment-term">
                                  <p class="s4 d-data">30 days</p>
                              </td>
                              <td class="discount-data">
                                  <p style="text-align: right;" class="s4 d-data">12%</p>
                              </td>
                          </tr>
                          <tr style="height: 14pt;">
                              <td class="payment-term">
                                  <p class="s4 d-data">7 days</p>
                              </td>
                              <td class="discount-data">
                                  <p style="text-align: right;" class="s4 d-data">14%</p>
                              </td>
                          </tr>
                      </table>
                      <p style="text-indent: 0pt; text-align: left;"><br /></p>
                      <ul>
                          <li data-list-text="*">
                              <p style="padding-left: 15pt; text-indent: -7pt; text-align: left;">Customers with higher annual sales volumes are entitled to discounts as per the slabs mentioned above.</p>
                          </li>
                          <li data-list-text="*">
                              <p style="padding-top: 2pt; padding-left: 15pt; text-indent: -7pt; text-align: left;">Annual Net Turnover means the total of all Monthly Net Turnover values (after adjusting MToD)</p>
                          </li>
                          <li data-list-text="*">
                              <p style="padding-top: 3pt; padding-left: 8pt; text-indent: 0pt; line-height: 114%; text-align: left;">
                                  If the Net Turonver falls in a particular slab, then discount will be calculated at the mentioned rate on the Total Sales starting from Rs.1/- and not on the Range difference only.
                              </p>
                          </li>
                          <li data-list-text="*">
                              <p style="padding-top: 3pt; padding-left: 15pt; text-indent: -7pt; text-align: left;">AToD will be credited to Customer Account by the Dealer no later than 7th of April.</p>
                              <p style="text-indent: 0pt; text-align: left;"><br /></p>
                              <p class="s5" style="padding-top: 5pt; padding-left: 8pt; text-indent: 0pt; text-align: left;">Company reserves the right to alter the discount structure any time.</p>
                          </li>
                      </ul>
                  </div>
                  </div> -->
            </div>
         </div>
      </div>
      <script>
         const items = document.querySelectorAll(".accordion button");
         
         function toggleAccordion() {
             const itemToggle = this.getAttribute("aria-expanded");
         
             for (i = 0; i < items.length; i++) {
                 items[i].setAttribute("aria-expanded", "false");
             }
         
             if (itemToggle == "false") {
                 this.setAttribute("aria-expanded", "true");
             }
         }
         
         items.forEach((item) => item.addEventListener("click", toggleAccordion));
      </script>
   </body>
</html>