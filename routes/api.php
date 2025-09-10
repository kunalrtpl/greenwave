<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});  
Route::namespace('api')->middleware(['api.log'])->group(function () {
	Route::post('/check-version','ApiController@checkVersion');
	Route::get('/cities','ApiController@cities');
	Route::post('/save-quick-enquiry', 'ApiController@saveQuickEnquiry');
	Route::post('/save-dealership-enquiry', 'ApiController@saveDealershipEnquiry');
	Route::post('/save-job-enquiry', 'ApiController@saveJobEnquiry');
	Route::post('/request-otp', 'ApiController@requestOtp');

	Route::get('/trial-reports-master','ApiController@trialReportsMaster');
	Route::get('/product-types','ApiController@productTypes');
	Route::get('/classes','ApiController@classes');
	Route::post('/delete-account','ApiController@deleteAccount');
	Route::match(['get', 'post'], '/categories','ApiController@categories');
	Route::match(['get', 'post'], '/products','ApiController@products');
	Route::match(['get', 'post'], '/monthly-turnover-discounts','ApiController@monthlyTurnoverDiscounts');
	Route::match(['get', 'post'], '/customer-mtod-spsod','ApiController@customerMtodSpsod');
	Route::match(['get', 'post'], '/generate-otp','ApiController@generateOtp');
	Route::match(['get', 'post'], '/register-request','ApiController@registerRequest');
	Route::match(['get', 'post'], '/notifications/{type}','ApiController@notifications');
	Route::match(['get', 'post'], '/other-master-list','ApiController@otherMasterList');
	Route::match(['get', 'post'], '/generate-product-pdf','ApiController@generateProductPdf');
	Route::prefix('dealer')->namespace('Dealers')->group(function(){
		Route::match(['get', 'post'], '/login','DealerController@login'); 
		Route::match(['get', 'post'], '/login-by-otp','DealerController@loginByOtp'); 
	});
	Route::middleware('DealerAuth')->group(function () {
		Route::prefix('dealer')->namespace('Dealers')->group(function(){
			Route::match(['get', 'post'], '/update-password','DealerController@updatePassword'); 
			Route::match(['get', 'post'], '/profile','DealerController@profile'); 
			Route::match(['get', 'post'], '/add-on-users','DealerController@addOnUsers'); 
			Route::match(['get', 'post'], '/add-edit-add-on-user','DealerController@addEditAddOnUser'); 
			Route::match(['get', 'post'], '/delete-add-on-user','DealerController@deleteAddonUser'); 
			Route::match(['get', 'post'], '/customers','DealerController@customers'); 
			Route::match(['get', 'post'], '/purchase-order','DealerController@purchaseOrder'); 
			Route::match(['get', 'post'], '/trader-purchase-order','DealerController@traderPurchaseOrder'); 
			Route::match(['get', 'post'], '/purchase-orders','DealerController@purchaseorderListing'); 
			Route::match(['get', 'post'], '/logout','DealerController@logout');
			Route::match(['get', 'post'], '/logout-all-devices','DealerController@logoutAllDevices');  
			Route::match(['get', 'post'], '/sale-invoice','DealerController@saleInvoice'); 
			Route::match(['get', 'post'], '/update-customer-payment-term','DealerController@updateCustomerPaymentTerm'); 
			Route::match(['get', 'post'], '/update-po','DealerController@updatePO'); 
			Route::match(['get', 'post'], '/po-adjustment','DealerController@purchaseOrderAdjustment');
			Route::match(['get', 'post'], '/delete-po','DealerController@deletePO');
			Route::match(['get', 'post'], '/in-transit-materials','DealerController@intransitMaterials');

			Route::match(['get', 'post'], '/v2-in-transit-materials','DealerController@v2intransitMaterials');

			Route::match(['get', 'post'], '/update-material-delivery','DealerController@updateMaterialDeliver');
			Route::match(['get', 'post'], '/fetch-products-stock','DealerController@fetchProductsStock');
			Route::match(['get', 'post'], '/update-products-stock','DealerController@updateProductsStock');
			Route::match(['get', 'post'], '/debit-credit-entry','DealerController@debitCreditEntry');
			Route::match(['get', 'post'], '/delete-debit-credit-entry/{id}','DealerController@deleteDebitCreditEntry');
			Route::match(['get', 'post'], '/get-debit-credit-account-of','DealerController@getDebitCreditAccountOf');
			Route::match(['get', 'post'], '/customer-purchase-return','DealerController@customerPurchaseReturn');
			Route::match(['get', 'post'], '/delete-customer-purchase-return','DealerController@deleteCustomerPurchaseReturn');
			Route::match(['get', 'post'], '/dealer-purchase-return','DealerController@dealerPurchaseReturn');
			Route::match(['get', 'post'], '/return-history','DealerController@return_history');
			Route::match(['get', 'post'], '/save-feedback','DealerController@saveFeedback');
			Route::match(['get', 'post'], '/market-products-info','DealerController@marketProductsInfo');
			Route::match(['get', 'post'], '/delete-market-products-info/{id}','DealerController@deleteMarketProductsInfo');
			Route::match(['get', 'post'], '/purchase-return-history','DealerController@dealerPurchaseReturnHistory');
			Route::match(['get', 'post'], '/stock-adjustment','DealerController@stockAdjustment');
			Route::match(['get', 'post'], '/stock-adjustment-logs','DealerController@stockAdjustmentLogs');
			Route::match(['get', 'post'], '/qcfs','DealerController@qcfs');
			Route::match(['get', 'post'], '/linked-dealers','DealerController@linkedDealers');
			Route::match(['get', 'post'], '/transfer-stock','DealerController@transferStock');
			Route::match(['get', 'post'], '/transfer-stock-history','DealerController@transferStockHistory');
			Route::match(['get', 'post'], '/discounts','DealerController@discounts');

			Route::match(['get', 'post'], '/create-sample-request','DealerController@createSampleRequest');
			Route::match(['get', 'post'], '/samplings','DealerController@samplings');
			Route::match(['get', 'post'], '/update-sample-material-delivery','DealerController@updateSampleMaterialDelivery');

			Route::match(['get', 'post'], '/material-approval','DealerController@materialApproval');
			Route::match(['get', 'post'], '/material-approval-list','DealerController@materialApprovalList');

			Route::match(['get', 'post'], '/material-approval-feedback','DealerController@materialApprovalFeedback');
			Route::match(['get', 'post'], '/linked-products','DealerController@linkedProducts');

			Route::match(['get', 'post'], 'market-samples','DealerController@marketSamples');
			Route::match(['get', 'post'], 'create-market-sample','DealerController@createMarketSample');
			Route::match(['get', 'post'], 'edit-market-sample','DealerController@editMarketSample');

			Route::match(['get', 'post'], 'complaint-samples','DealerController@complaintSamples');
			Route::match(['get', 'post'], 'create-complaint-sample','DealerController@createComplaintSample');
			Route::match(['get', 'post'], 'edit-complaint-sample','DealerController@editComplaintSample');
			Route::match(['get', 'post'], 'sample-in-transit-materials','DealerController@sampleInTransitMaterials');

			Route::match(['get', 'post'], 'feedback-histories','DealerController@feedbackHistory');
			Route::match(['get', 'post'], 'delete-sale-invoice/{id}','DealerController@deleteSaleInvoice');
			Route::match(['get', 'post'], 'delete-bulk-sale-invoice','DealerController@deleteBulkSaleInvoice');
			Route::match(['get', 'post'], 'customer-invoice-sales','DealerController@customerInvoiceSales');

			Route::match(['get', 'post'], 'update-customer-latitude-longitude','DealerController@updateCustomerLatitudeLongitude');


			Route::match(['get', 'post'], 'save-purchase-projection','DealerController@savePurchaseProjection');
			Route::match(['get', 'post'], 'get-purchase-projection','DealerController@getPurchaseProjections');
			Route::match(['get', 'post'], 'get-monthly-projection-status','DealerController@getMonthlyProjectionStatus');
			Route::match(['get', 'post'], 'update-purchase-projection-action','DealerController@updatePurchaseProjectionAction');


			Route::match(['get', 'post'], 'get-monthly-sales-projection-status','DealerController@getMonthlySalesProjectionStatus');

			Route::match(['get', 'post'], 'get-sales-projection','DealerController@getSalesProjections');
			Route::match(['get', 'post'], 'purchase-data','DealerController@purchase_data');
		});
	});

	Route::prefix('customer')->namespace('Customers')->group(function(){
		Route::match(['get', 'post'], '/login','CustomerContoller@login'); 
		Route::middleware('CustomerAuth')->group(function () {
			Route::match(['get', 'post'], '/logout','CustomerContoller@logout'); 
			Route::match(['get', 'post'], '/profile','CustomerContoller@profile'); 
			Route::match(['get', 'post'], '/purchase-order','CustomerContoller@purchaseOrder');
			Route::match(['get', 'post'], '/customer-info','CustomerContoller@customerinfo');

			Route::match(['get', 'post'], '/purchase-orders','CustomerContoller@purchaseorderListing');
			Route::match(['get', 'post'], '/employees','CustomerContoller@customerEmployees');
			Route::match(['get', 'post'], '/add-employee','CustomerContoller@addCustomerEmployee');
			Route::match(['get', 'post'], '/delete-employee','CustomerContoller@deleteCustomerEmp');
			Route::match(['get', 'post'], '/return-history','CustomerContoller@customer_return_history');

			Route::match(['get', 'post'], '/save-feedback','CustomerContoller@saveFeedback');
			Route::match(['get', 'post'], '/qcfs','CustomerContoller@qcfs');

			Route::match(['get', 'post'], '/get-debit-credit-account-of','CustomerContoller@getDebitCreditAccountOf');

			Route::match(['get', 'post'], '/delete-po','CustomerContoller@deletePO');
		});
	});


	Route::prefix('executive')->namespace('User')->group(function(){
		Route::match(['get', 'post'], '/login','ExecutiveController@login'); 
		Route::match(['get', 'post'], '/login-by-otp','ExecutiveController@loginByOtp'); 
		Route::middleware('CustomerAuth')->group(function () {
			Route::match(['get', 'post'], '/logout','ExecutiveController@logout'); 
			Route::match(['get', 'post'], '/logout-all-devices','ExecutiveController@logoutAllDevices'); 
			Route::match(['get', 'post'], '/profile','ExecutiveController@profile'); 
			Route::match(['get', 'post'], '/subordinate-profile','ExecutiveController@subordinateProfile'); 
			Route::match(['get', 'post'], '/update-password','ExecutiveController@updatePassword'); 
			Route::match(['get', 'post'], '/products','ExecutiveController@products'); 
			Route::match(['get', 'post'], '/customers','ExecutiveController@customers'); 
			Route::match(['get', 'post'], '/fetch-customers','ExecutiveController@fetchCustomers'); 
			Route::match(['get', 'post'], '/fetch-customers-grouped-by-users','ExecutiveController@fetchCustomersGroupedByUsers'); 
			Route::match(['get', 'post'], '/purchase-order','ExecutiveController@purchaseOrder'); 
			Route::match(['get', 'post'], '/purchase-orders','ExecutiveController@purchaseorderListing');

			Route::match(['get', 'post'], '/create-sample-request','ExecutiveController@createSampleRequest');
			Route::match(['get', 'post'], '/samplings','ExecutiveController@samplings'); 

			Route::match(['get', 'post'], '/market-products-info','ExecutiveController@marketProductsInfo');
			Route::match(['get', 'post'], '/delete-market-products-info/{id}','ExecutiveController@deleteMarketProductsInfo');

			Route::match(['get', 'post'], '/save-feedback','ExecutiveController@saveFeedback');

			Route::match(['get', 'post'], '/qcfs','ExecutiveController@qcfs');
			Route::match(['get', 'post'], '/update-qcfs-status','ExecutiveController@updateQcfsStatus');
			Route::match(['get', 'post'], '/feedback-reply','ExecutiveController@feedbackReply');
			Route::match(['get', 'post'], '/save-dvr','ExecutiveController@saveDvr');
			Route::match(['get', 'post'], '/upload-dvr-media','ExecutiveController@uploadDvrMedia');
			Route::match(['get', 'post'], '/dvrs','ExecutiveController@dvrs');
			Route::match(['get', 'post'], '/dvr/{id}','ExecutiveController@dvrInfo');
			Route::match(['get', 'post'], '/update-dvr-can-share','ExecutiveController@updateDvrCanShare');
			Route::match(['get', 'post'], '/delete-dvr/{id}','ExecutiveController@deleteDvr');
			Route::match(['get', 'post'], 'link-unlink-dvr-scheduler','ExecutiveController@linkUnlinkDvrScheduler');

			Route::match(['get', 'post'], '/linked-employees','ExecutiveController@linkedEmployees');
			Route::match(['get', 'post'], '/master-lists','ExecutiveController@masterlists');
			Route::match(['get', 'post'], '/save-customer-request','ExecutiveController@saveCustomerRequest');
			Route::match(['get', 'post'], '/v2-save-customer-request','ExecutiveController@v2saveCustomerRequest');
			Route::match(['get', 'post'], '/customer-register-info','ExecutiveController@customerRegisterInfo');

			Route::match(['get', 'post'], '/customers-area-list','ExecutiveController@customersAreaList');
			Route::match(['get', 'post'], '/added-customer-requests','ExecutiveController@addedCustomerRequests');

			Route::match(['get', 'post'], '/return-history','ExecutiveController@return_history');
			Route::match(['get', 'post'], '/save-lost-sale-report','ExecutiveController@saveLostSaleReport');
			Route::match(['get', 'post'], '/lost-sale-reports','ExecutiveController@lostSaleReports');
			Route::match(['get', 'post'], '/edit-lost-sale-report','ExecutiveController@editLostSaleReport');
			Route::match(['get', 'post'], '/delete-lost-sale-report/{id}','ExecutiveController@deleteLostSaleReport');
			Route::match(['get', 'post'], '/material-approval-list','ExecutiveController@materialApprovalList');
			Route::match(['get', 'post'], '/products-free-sample-stock','ExecutiveController@productsFreeSampleStock');
			Route::match(['get', 'post'], '/sample-submission','ExecutiveController@sampleSubmission');
			Route::match(['get', 'post'], '/update-sample-submission-values','ExecutiveController@updateSampleSubmissionValues');
			Route::match(['get', 'post'], '/sample-in-transit-materials','ExecutiveController@sampleInTransitMaterials');
			Route::match(['get', 'post'], '/update-free-sample-material-delivery','ExecutiveController@updateFreeSampleMaterialDelivery');
			Route::match(['get', 'post'], '/sample-submission-list','ExecutiveController@sampleSubmissionList');
			Route::match(['get', 'post'], '/add-sample-submission-feedback','ExecutiveController@addSampleSubsmissionFeedback');
			Route::match(['get', 'post'], '/close-sample-submission','ExecutiveController@closeSampleSubmission');
			Route::match(['get', 'post'], '/return-sample-submission','ExecutiveController@returnSampleSubmission');
			Route::match(['get', 'post'], '/sample-stock-adjustment','ExecutiveController@sampleStockAdjustment');
			Route::match(['get', 'post'], '/sample-stock-adjustment-logs','ExecutiveController@sampleStockAdjustmentLogs');
			Route::match(['get', 'post'], 'market-samples','ExecutiveController@marketSamples');
			Route::match(['get', 'post'], 'create-market-sample','ExecutiveController@createMarketSample');
			Route::match(['get', 'post'], 'edit-market-sample','ExecutiveController@editMarketSample');


			Route::match(['get', 'post'], 'complaint-samples','ExecutiveController@complaintSamples');
			Route::match(['get', 'post'], 'create-complaint-sample','ExecutiveController@createComplaintSample');
			Route::match(['get', 'post'], 'edit-complaint-sample','ExecutiveController@editComplaintSample');

			Route::match(['get', 'post'], 'schedulers','ExecutiveController@schedulers');
			Route::match(['get', 'post'], 'create-scheduler','ExecutiveController@createScheduler');
			Route::match(['get', 'post'], 'edit-scheduler','ExecutiveController@editScheduler');
			Route::match(['get', 'post'], 'update-scheduler-status','ExecutiveController@updateSchedulerStatus');
			Route::match(['get', 'post'], 'update-next-scheduler','ExecutiveController@updateNextScheduler');
			Route::match(['get', 'post'], 'delete-scheduler/{id}','ExecutiveController@deleteScheduler');


			Route::match(['get', 'post'], 'trial-reports','ExecutiveController@trialReports');
			Route::match(['get', 'post'], 'create-trial-report','ExecutiveController@createTrialReport');
			Route::match(['get', 'post'], 'edit-trial-report','ExecutiveController@editTrialReport');
			Route::match(['get', 'post'], 'delete-trial-report/{id}','ExecutiveController@deleteTrialReport');
			Route::match(['get', 'post'], 'update-trial-report-status','ExecutiveController@updateTrialReportStatus');
			Route::match(['get', 'post'], 'generate-trial-report-pdf','ExecutiveController@generateTrialReportPdf');
			Route::match(['get', 'post'], 'update-trial-report-can-share','ExecutiveController@updateTrialReportCanShare');
			Route::match(['get', 'post'], 'link-dvr-trialreport','ExecutiveController@linkDvrTrial');
			Route::match(['get', 'post'], 'dealers','ExecutiveController@dealers');
			Route::match(['get', 'post'], 'feedback-histories','ExecutiveController@feedbackHistory');

			Route::match(['get', 'post'], 'customer-invoice-sales','ExecutiveController@customerInvoiceSales');
			Route::match(['get', 'post'], 'direct-customer-products/{customerid}','ExecutiveController@directCustomerProducts');
			Route::match(['get', 'post'], 'save-sales-projection','ExecutiveController@saveSalesProjection');
			Route::match(['get', 'post'], 'get-sales-projection','ExecutiveController@getSalesProjections');
			Route::match(['get', 'post'], 'get-monthly-projection-status','ExecutiveController@getMonthlyProjectionStatus');
			Route::match(['get', 'post'], 'update-sales-projection-action','ExecutiveController@updateSalesProjectionAction');
			Route::match(['get', 'post'], 'update-customer-latitude-longitude','ExecutiveController@updateCustomerLatitudeLongitude');
		});
	});
});