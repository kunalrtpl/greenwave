<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/discounts', function () {
    return view('discounts');
});
Route::get('/contact-us', function () {
    return view('contact-us');
});
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
});
Route::get('data-safety', function () {
    return view('data-safety');
});
Auth::routes();
Route::prefix('/admin')->namespace('Admin')->group(function(){
  //All the admin routes will be defined here...
	Route::match(['get','post'],'/email','AdminController@emaillogin');
	Route::match(['get', 'post'], '/', 'AdminController@login')->name('admin.login');
	Route::post('/send-otp', 'AdminController@sendOtp')->name('admin.send.otp');
	Route::get('logout','AdminController@logout');
	Route::group(['middleware' => ['user']], function () {
		Route::match(['get', 'post'], '/dashboard', 'AdminController@dashboard');
		Route::match(['get', 'post'], '/profile', 'AdminController@profile');
		Route::match(['get', 'post'], '/settings', 'AdminController@settings');
		Route::match(['get', 'post'], '/change-picture', 'AdminController@changeAdminLogo');
		Route::match(['get', 'post'], '/update-password', 'AdminController@changeAdminPassword');
		Route::match(['get', 'post'], '/checkAdminPassword', 'AdminController@checkAdminPassword');
		Route::match(['get', 'post'], '/status', 'AdminController@status');

		Route::match(['get', 'post'], '/dvrs', 'DataController@dvrs');

		Route::match(['get', 'post'], '/categories', 'DataController@categories');
		Route::match(['get', 'post'], '/add-edit-category/{id?}', 'DataController@addEditCategory');
		Route::match(['get', 'post'], '/save-category', 'DataController@saveCategory');

		Route::match(['get', 'post'], '/make', 'DataController@make');
		Route::match(['get', 'post'], '/add-edit-make/{id?}', 'DataController@addEditMake');
		Route::match(['get', 'post'], '/save-make', 'DataController@saveMake');

		/*Department Routes Starts*/
		Route::match(['get', 'post'], '/departments', 'DepartmentController@departments');
		Route::match(['get', 'post'], '/add-edit-department/{id?}', 'DepartmentController@addEditDepartment');
		Route::match(['get', 'post'], '/save-department', 'DepartmentController@saveDepartment');
		/*Department Routes Ends*/

		/*Designations Routes Starts*/
		Route::match(['get', 'post'], '/designations', 'DesignationController@designations');
		Route::match(['get', 'post'], '/add-edit-designation/{id?}', 'DesignationController@addEditDesignation');
		Route::match(['get', 'post'], '/save-designation', 'DesignationController@saveDesignation');
		Route::match(['get', 'post'], '/get-dept-designation', 'DesignationController@getDeptDesignations');
		/*Designations Routes Ends*/

		/*Users Routes Starts*/
		Route::match(['get', 'post'], '/users', 'UsersController@users');
		Route::match(['get', 'post'], '/add-edit-user/{id?}', 'UsersController@addEditUser');
		Route::match(['get', 'post'], '/save-user', 'UsersController@saveUser');
		Route::match(['get', 'post'], 'user-reset-pin/{userid}', 'UsersController@resetPin');
		Route::match(['get', 'post'], '/open-user-dept-modal', 'UsersController@openUserDeptModal');
		Route::match(['get', 'post'], '/append-designation-info', 'UsersController@appendDesignationInfo');
		Route::match(['get', 'post'], '/get-sub-regions', 'UsersController@getSubRegions');
		Route::match(['get', 'post'], '/add-user-dept-designation', 'UsersController@addUserDeptDesignation');
		Route::match(['get', 'post'], '/append-customers', 'UsersController@appendCustomers');
		Route::match(['get', 'post'], '/update-role/{id}', 'UsersController@updateRole');
		/*Users Routes Ends*/

		Route::match(['get', 'post'], '/user-incentives', 'UsersController@userIncentives');
		Route::match(['get', 'post'], '/add-edit-user-incentive/{id?}', 'UsersController@addEditUserIncentive');
		Route::match(['get', 'post'], '/save-user-incentive', 'UsersController@saveUserIncentive');
		Route::match(['get', 'post'], '/delete-user-incentive/{id}', 'UsersController@deleteUserIncentive');

		Route::match(['get', 'post'], '/customer-register-requests', 'UsersController@customerRegisterRequests');
		Route::match(['get', 'post'], '/close-customer-register-request/{id}', 'UsersController@closeCustomerRegisterRequest');
		Route::match(['get', 'post'], '/customer-register-request/{id}', 'UsersController@showCustomerRegisterRequestDetails');
		Route::match(['get', 'post'], '/customer-register-request/{id}/verify', 'UsersController@verifyCustomerRequest');

		/*Country Routes Starts*/
		Route::match(['get', 'post'], '/countries', 'CountryController@countries');
		Route::match(['get', 'post'], '/add-edit-country/{id?}', 'CountryController@addEditCountry');
		Route::match(['get', 'post'], '/save-country', 'CountryController@saveCountry');
		/*Country Routes Ends*/

		/*State Routes Starts*/
		Route::match(['get', 'post'], '/states', 'StateController@states');
		Route::match(['get', 'post'], '/add-edit-state/{id?}', 'StateController@addEditState');
		Route::match(['get', 'post'], '/save-state', 'StateController@saveState');
		Route::match(['get', 'post'], '/get-states', 'StateController@getStates');
		/*State Routes Ends*/

		/*City Routes Starts*/
		Route::match(['get', 'post'], '/cities', 'CityController@cities');
		Route::match(['get', 'post'], '/add-edit-city/{id?}', 'CityController@addEditCity');
		Route::match(['get', 'post'], '/save-city', 'CityController@saveCity');
		Route::match(['get', 'post'], '/get-cities', 'CityController@getCities');
		/*City Routes Ends*/

		/*Region  Routes Starts*/
		Route::match(['get', 'post'], '/regions', 'RegionController@regions');
		Route::match(['get', 'post'], '/add-edit-region/{id?}', 'RegionController@addEditRegion');
		Route::match(['get', 'post'], '/save-region', 'RegionController@saveRegion');
		Route::match(['get', 'post'], '/get-state-cities', 'RegionController@getStateCities');
		/*Region Routes Ends*/


		Route::match(['get', 'post'], '/lost-sales-info', 'DataController@lostSalesInfo');
		Route::match(['get', 'post'], '/lost-sales-info-detail', 'DataController@lostSalesInfoDetail');

		/*Dealer Routes Starts*/
		Route::match(['get', 'post'], '/dealers', 'DealerController@dealers');
		Route::match(['get', 'post'], '/add-edit-dealer/{id?}', 'DealerController@addEditDealer');
		Route::match(['get', 'post'], '/save-dealer', 'DealerController@saveDealer');
		Route::match(['get', 'post'], 'dealer-reset-pin/{dealerid}', 'DealerController@resetPin');
		Route::match(['get', 'post'], '/manage-dealer-stock/{id}', 'DealerController@manageDealerStock');
		Route::match(['get', 'post'], '/dealer-special-discount/{id}', 'DealerController@dealerSpecialDiscount');

		//Dealer Market Product Infos
		Route::match(['get', 'post'], '/dealer/market-product-infos', 'DealerController@marketProductInfos');
		

		Route::match(['get', 'post'], '/qty-discounts', 'DealerController@qtyDiscounts');
		Route::match(['get', 'post'], '/add-edit-qty-discount/{id?}', 'DealerController@addEditQtyDiscount');
		Route::match(['get', 'post'], '/save-qty-discount', 'DealerController@saveProductDiscount');
		Route::match(['get', 'post'], '/delete-qty-discount/{id}', 'DealerController@deleteQtyDiscount');

		Route::match(['get', 'post'], '/dealer-users/{dealerid}', 'DealerController@dealerUsers');
		Route::match(['get', 'post'], '/add-edit-dealer-user/{id?}', 'DealerController@addEditDealerUser');
		Route::match(['get', 'post'], '/save-dealer-user', 'DealerController@saveDealerUser');
		Route::match(['get', 'post'], '/delete-dealer-user/{id}', 'DealerController@deleteDealerUser');

		/*Dealer  Routes Ends*/

		/*Dealer Routes Starts*/
		Route::match(['get', 'post'], '/dealer-incentives', 'DealerController@dealerIncentives');
		Route::match(['get', 'post'], '/add-edit-dealer-incentive/{id?}', 'DealerController@addEditDealerIncentive');
		Route::match(['get', 'post'], '/save-dealer-incentive', 'DealerController@saveDealerIncentive');
		Route::match(['get', 'post'], '/delete-dealer-incentive/{id}', 'DealerController@deleteDealerIncentive');

		Route::match(['get', 'post'], '/dealer-atod', 'DealerController@dealerAtod');
		Route::match(['get', 'post'], '/add-edit-atod/{id?}', 'DealerController@addEditAtod');
		Route::match(['get', 'post'], '/save-atod', 'DealerController@saveAtod');
		Route::match(['get', 'post'], '/delete-atod/{id}', 'DealerController@deleteAtod');
		/*Dealer  Routes Ends*/

		/*Raw Material Routes Starts*/
		Route::match(['get', 'post'], '/raw-materials', 'ProductsController@rawMaterials');
		Route::match(['get', 'post'], '/add-edit-raw-material/{id?}', 'ProductsController@addEditRawMaterial');
		Route::match(['get', 'post'], '/save-raw-material', 'ProductsController@saveRawMaterial');
		Route::match(['get', 'post'], '/delete-product-document/{type}/{proid}', 'ProductsController@deleteProductDocument');
		/*Raw Material  Routes Ends*/

		/*Packing Sizes Routes Starts*/
		Route::match(['get', 'post'], '/packing-sizes', 'ProductsController@packingSizes');
		Route::match(['get', 'post'], '/add-edit-packing-size/{id?}', 'ProductsController@addEditPckingSize');
		Route::match(['get', 'post'], '/save-packing-size', 'ProductsController@savePackingSize');
		Route::match(['get', 'post'], '/delete-packing-size/{id}', 'ProductsController@deletePackingSize');
		/*Packing Sizes  Routes Ends*/

		/*Labels Routes Starts*/
		Route::match(['get', 'post'], '/labels', 'LabelController@labels');
		Route::match(['get', 'post'], '/add-edit-label/{id?}', 'LabelController@addEditLabel');
		Route::match(['get', 'post'], '/save-label', 'LabelController@saveLabel');
		/*Labels Routes Ends*/


		Route::match(['get', 'post'], '/machines', 'MachineController@machines');
		Route::match(['get', 'post'], '/add-edit-machine/{id?}', 'MachineController@addEditMachine');
		Route::match(['get', 'post'], '/save-machine', 'MachineController@saveMachine');
		Route::match(['get', 'post'], '/delete-machine/{id}', 'MachineController@deleteMachine');

		/*Packing Types Routes Starts*/
		Route::match(['get', 'post'], '/packing-types', 'ProductsController@packingTypes');
		Route::match(['get', 'post'], '/add-edit-packing-type/{id?}', 'ProductsController@addEditPackingType');
		Route::match(['get', 'post'], '/save-packing-type', 'ProductsController@savePackingType');
		/*Packing Types  Routes Ends*/

		Route::match(['get', 'post'], '/product-class', 'ProductClassController@productClass');
		Route::match(['get', 'post'], '/add-edit-product-class/{id?}', 'ProductClassController@addEditProductClass');
		Route::match(['get', 'post'], '/save-product-class', 'ProductClassController@saveProductClass');
		Route::match(['get', 'post'], '/delete-product-class/{id}', 'ProductClassController@deleteProductClass');

		Route::match(['get', 'post'], '/checklists', 'ChecklistController@checklists');
		Route::match(['get', 'post'], '/add-edit-checklist/{id?}', 'ChecklistController@addEditChecklist');
		Route::match(['get', 'post'], '/save-checklist', 'ChecklistController@saveChecklist');
		/*Product Routes Starts*/
		Route::match(['get', 'post'], '/products', 'ProductsController@products');
		Route::match(['get', 'post'], '/add-edit-product/{id?}', 'ProductsController@addEditProduct');
		Route::match(['get', 'post'], '/save-product', 'ProductsController@saveProduct');
		Route::match(['get', 'post'], '/product-qc/{id}', 'ProductsController@productQc');
		Route::match(['get', 'post'], '/product-costing/{id}', 'ProductsController@productCosting');
		Route::match(['get', 'post'], '/get-product-inherit-layout', 'ProductsController@getProductInheritLayout');
		Route::match(['get', 'post'], '/add-more-raw-material', 'ProductsController@addMoreRawMaterial');
		Route::match(['get', 'post'], '/calculate-rm-cost', 'ProductsController@calculateRMCost');
		/*Product Routes Ends*/


		Route::get(
		    'additional-cost',
		    'AdditionalCostController@index'
		)->name('admin.additional-cost.index');

		// Page 2 – preview (GET with product id)
		Route::get(
		    'additional-cost/preview/{product}',
		    'AdditionalCostController@preview'
		)->name('admin.additional-cost.preview');


		Route::match(['get', 'post'], '/product-discounts', 'ProductsController@productDiscounts');
		Route::match(['get', 'post'], '/add-edit-product-discount/{id?}', 'ProductsController@addEditProductDiscount');
		Route::match(['get', 'post'], '/save-product-discount', 'ProductsController@saveProductDiscount');
		Route::match(['get', 'post'], '/delete-product-discount/{disid}', 'ProductsController@deleteProductDiscount');


		/*Customer Routes Starts*/
		Route::match(['get', 'post'], '/customers', 'CustomerController@customers');
		Route::match(['get', 'post'], '/add-edit-customer/{id?}', 'CustomerController@addEditCustomer');
		Route::match(['get', 'post'], '/save-customer', 'CustomerController@saveCustomer');
		Route::match(['get', 'post'], '/append-discount-details', 'CustomerController@appedDiscountDetails');
		Route::match(['get', 'post'], '/add-customer-discount', 'CustomerController@addCustomerDiscount');
		Route::match(['get', 'post'], '/append-marketing-users', 'CustomerController@appendMarketingUsers');
		Route::match(['get', 'post'], '/register-requests', 'CustomerController@registerRequests');
		Route::match(['get', 'post'], '/delete-register-request/{id}', 'CustomerController@deleteRegisterRequest');
		Route::match(['get', 'post'], '/customer/fetch-products-by-type', 'CustomerController@fetchProductsByType')->name('customer.fetch.products.by.type');

		/*Customer  Routes Ends*/

		Route::match(['get', 'post'], '/customer-discounts', 'CustomerController@customerDiscounts');
		Route::match(['get', 'post'], '/add-edit-customer-discount/{id?}', 'CustomerController@addEditCustomerDiscount');
		Route::match(['get', 'post'], '/save-customer-discount', 'CustomerController@saveCustomerDiscount');
		Route::match(['get', 'post'], '/delete-customer-discount/{disid}', 'CustomerController@deleteCustomerDiscount');

		//Purchase and Sale Order Detail
		Route::match(['get', 'post'], '/customer-purchase-order-detail/{id}', 'OrdersController@customerPurchaseOrderDetail');
		Route::match(['get', 'post'], '/dealer-purchase-order-detail/{id}', 'OrdersController@dealerPurchaseOrderDetail');
		Route::match(['get', 'post'], '/update-dealer-po-status', 'OrdersController@UpdateDealerPoStatus');
		Route::match(['get', 'post'], '/update-dealer-po-qty', 'OrdersController@UpdateDealerPoQty');
		Route::match(['get', 'post'], '/open-po-adjust-modal', 'OrdersController@openPoAdjustModal');
		Route::match(['get', 'post'], '/update-po-adjustment', 'OrdersController@updatePoAdjustment');
		Route::match(['get', 'post'], '/link-dealer-product', 'OrdersController@linkDealerProduct');
		Route::match(['get', 'post'], '/mark-urgent-po-item', 'OrdersController@markUrgentPoItem');
		Route::match(['get', 'post'], '/mark-po-item-on-hold-date', 'OrdersController@markPoItemOnHold');
		Route::match(['get', 'post'], '/fetch-product-qty-discounts', 'OrdersController@fetchProductQtyDiscounts');

		//Inventory Management
		Route::match(['get', 'post'], '/add-incoming-material', 'InventoryController@addIncomingMaterial');
		Route::match(['get', 'post'], '/append-material-details', 'InventoryController@appendMaterialDetails');
		Route::match(['get', 'post'], '/inventory/rm', 'InventoryController@rawMaterials');
		Route::match(['get', 'post'], '/inventory/update-rm-status/{id}', 'InventoryController@updateRmStatus');
		Route::match(['get', 'post'], '/rm-inventory-media/{id}', 'InventoryController@rmInventoryMedia');
		Route::match(['get', 'post'], '/save-rm-inventory-media', 'InventoryController@saveRmInventoryMedia');
		Route::match(['get', 'post'], '/delete-rm-inventory-media/{mediaid}', 'InventoryController@deleteRmInventoryMedia');
		Route::match(['get', 'post'], '/inventory/material-tracking', 'InventoryController@rawMaterialTracking');
		Route::match(['get', 'post'], '/inventory/rm-pdf/{id}/{type}', 'InventoryController@downloadRMpdf');

		Route::match(['get', 'post'], '/inventory/pm', 'InventoryController@packingMaterials');
		Route::match(['get', 'post'], '/inventory/rm-samples/{id}', 'InventoryController@rmSamples');
		Route::match(['get', 'post'], '/inventory/rm-packing-labels/{id}', 'InventoryController@rmPackingLabel');

		Route::match(['get', 'post'], '/inventory/osp', 'InventoryController@ospProducts');
		Route::match(['get', 'post'], '/inventory/update-osp-status/{id}', 'InventoryController@updateOspStatus');
		Route::match(['get', 'post'], '/inventory/osp-tracking', 'InventoryController@ospTracking');
		Route::match(['get', 'post'], '/inventory/osp-pdf/{id}/{type}', 'InventoryController@downloadOSPpdf');

		Route::match(['get', 'post'], '/osp-media/{id}', 'InventoryController@ospMedia');
		Route::match(['get', 'post'], '/save-osp-media', 'InventoryController@saveOspMedia');
		Route::match(['get', 'post'], '/delete-osp-media/{mediaid}', 'InventoryController@deleteOspMedia');

		Route::match(['get', 'post'], '/inventory/osp-samples/{id}', 'InventoryController@ospSamples');
		Route::match(['get', 'post'], '/inventory/osp-packing-labels/{id}', 'InventoryController@ospPackingLabel');
		Route::match(['get', 'post'], '/inventory/get-product-labels', 'InventoryController@getProductLabels');


		//Notification Route
		Route::match(['get', 'post'], '/save-notification-token', 'NotificationController@saveNotificationToken');

		//Orders Route
		Route::match(['get', 'post'], '/customer-orders', 'OrdersController@customerOrders');
		Route::match(['get', 'post'], '/dealer-orders', 'OrdersController@dealerOrders');
		Route::match(['get', 'post'], '/po-dispatch-planning', 'OrdersController@POdispatchPlanning');
		Route::match(['get', 'post'], '/get-product-batches', 'OrdersController@getProductBatches');
		Route::match(['get', 'post'], '/update-product-dispatch-qty', 'OrdersController@UpdateProductDispatchQty');
		Route::match(['get', 'post'], '/update-pro-dispatch-qty', 'OrdersController@UpdateProDispatchQty');

		Route::match(['get', 'post'], '/finalize-do', 'OrdersController@finalizeDo');
		Route::match(['get', 'post'], '/undo-finalize-do/{saleinvoiceid}/{poitemid}', 'OrdersController@undoFinalizeDo');
		Route::match(['get', 'post'], '/generate-do-numbers', 'OrdersController@generateDoNumbers');
		Route::match(['get', 'post'], '/do-ready', 'OrdersController@DoReady');
		Route::match(['get', 'post'], '/update-sale-invoice', 'OrdersController@updateSaleInvoice');
		Route::match(['get', 'post'], '/update-bulk-sale-invoice', 'OrdersController@updateBulkSaleInvoice');
		Route::match(['get', 'post'], '/bill-ready', 'OrdersController@BillReady');
		Route::match(['get', 'post'], '/dispatched-material', 'OrdersController@dispatchedMaterial');
		Route::match(['get', 'post'], '/update-transport-details', 'OrdersController@updateTransportDetails');
		Route::match(['get', 'post'], '/update-bulk-transport-details', 'OrdersController@updateBulkTransportDetails');

		Route::match(['get', 'post'], '/direct-customer-orders', 'DirectCustomerController@directCustomerOrders');
		Route::match(['get', 'post'], '/direct-customer-purchase-order-detail/{id}', 'DirectCustomerController@directCustomerPurchaseOrderDetail');
		Route::match(['get', 'post'], '/update-direct-customer-po-qty', 'DirectCustomerController@UpdateDirectCustomerPoQty');

		//Planning Sheet
		Route::match(['get', 'post'], '/pending-order', 'PlanController@pendingorder');

		Route::match(['get', 'post'], '/trader-orders', 'OrdersController@traderOrders');
		Route::match(['get', 'post'], '/trader-purchase-order-detail/{id}', 'OrdersController@traderPurchaseOrderDetail');
		Route::match(['get', 'post'], '/update-trader-po-qty', 'OrdersController@UpdateTraderPoPoQty');
		Route::match(['get', 'post'], '/open-trader-sale-invoice-modal', 'OrdersController@openTraderSaleInvoice');
		Route::match(['get', 'post'], '/create-trader-sale-invoice', 'OrdersController@createTraderSaleInvoice');

		Route::match(['get', 'post'], '/create-production-job-card', 'JobCardController@createProductionJobCard');
		Route::match(['get', 'post'], '/append-standard-recipe', 'JobCardController@appendStandardRecipe');
		Route::match(['get', 'post'], '/append-requirement-list', 'JobCardController@appendRequirementList');
		Route::match(['get', 'post'], '/add-rm-requirement', 'JobCardController@addRmRequirement');

		/*Batch Sheet Routes*/
		Route::match(['get', 'post'], '/create-batch-sheet', 'BatchSheetController@createBatchSheet');
		Route::match(['get', 'post'], '/append-batch-rm-requirements', 'BatchSheetController@batchRmRequirements');
		Route::match(['get', 'post'], '/batch-sheets', 'BatchSheetController@batchSheets');
		Route::match(['get', 'post'], '/batchsheet/batch-tracking', 'BatchSheetController@batchSheetTracking');
		Route::match(['get', 'post'], '/update-batch-sheet/{id}', 'BatchSheetController@updateBatchSheet');

		Route::match(['get', 'post'], '/batch-sheet-media/{id}', 'BatchSheetController@batchSheetMedia');
		Route::match(['get', 'post'], '/save-batch-sheet-media', 'BatchSheetController@saveBatchSheetMedia');
		Route::match(['get', 'post'], '/delete-batch-sheet-media/{mediaid}', 'BatchSheetController@deleteBatchSheetMedia');


		Route::match(['get', 'post'], '/batch-packing-labels/{id}', 'BatchSheetController@bactchpackingLabels');

		//Incoming Entry Controller
		Route::match(['get', 'post'], '/entry/rm', 'IncomingentryController@rawMaterialsEntry');
		Route::match(['get', 'post'], 'delete-rm-entry/{id}', 'IncomingentryController@deleteRmEntry');
		Route::match(['get', 'post'], '/entry/srm', 'IncomingentryController@srmProductsEntry');
		Route::match(['get', 'post'], '/entry/rfdm', 'IncomingentryController@rfdmProductsEntry');
		Route::match(['get', 'post'], '/entry/ihp', 'IncomingentryController@ihpProductsEntry');
		Route::match(['get', 'post'], 'delete-osp-entry/{id}', 'IncomingentryController@deleteOspEntry');
		Route::match(['get', 'post'], 'entry/labels', 'IncomingentryController@labelsEntry');

		Route::match(['get', 'post'], '/qcfs', 'AdminController@qcfs');
		Route::match(['get', 'post'], '/qcfs-reply/{id}', 'AdminController@qcfsReply');


		Route::match(['get', 'post'], '/notifications', 'NotificationController@notifications');
		Route::match(['get', 'post'], '/add-edit-notification/{id?}', 'NotificationController@addEditNotification');
		Route::match(['get', 'post'], '/save-notification', 'NotificationController@saveNotification');
		Route::match(['get', 'post'], '/send-push-notification', 'NotificationController@sendPushnotification');
		Route::match(['get', 'post'], '/process-notifications', 'NotificationController@processNotification');


		Route::match(['get', 'post'], '/quick-enquiries', 'EnquiryController@quickEnquiries');
		Route::match(['get', 'post'], '/dealership-enquiries', 'EnquiryController@dealershipEnquiries');
		Route::match(['get', 'post'], '/job-enquiries', 'EnquiryController@jobEnquiries');

		//Sampling routes
		Route::match(['get', 'post'], '/free-sampling', 'SamplingController@freeSampling');
		Route::match(['get', 'post'], '/paid-sampling', 'SamplingController@paidSampling');
		Route::match(['get', 'post'], '/free-sampling-detail/{id}', 'SamplingController@freeSamplingDetail');
		Route::match(['get', 'post'], '/paid-sampling-detail/{id}', 'SamplingController@paidSamplingDetail');
		Route::match(['get', 'post'], '/mark-urgent-sample-item', 'SamplingController@markUrgentSampleItem');
		Route::match(['get', 'post'], '/update-sampling-status', 'SamplingController@updateSamplingStatus');
		Route::match(['get', 'post'], '/update-sampling-qty', 'SamplingController@updateSamplingQty');

		Route::match(['get', 'post'], '/sampling-dispatch-planning', 'SamplingController@samplingDispatchPlanning');
		Route::match(['get', 'post'], '/update-pro-sample-dispatch-qty', 'SamplingController@updateProSampleDispatchQty');
		Route::match(['get', 'post'], '/sample-finalize-do/{type}', 'SamplingController@sampleFinalizeDo');
		Route::match(['get', 'post'], '/undo-sampling-finalize-do/{saleinvoiceid}/{poitemid}', 'SamplingController@undoSampleFinalizeDO');
		Route::match(['get', 'post'], '/sampling-generate-do-numbers', 'SamplingController@samplingGenerateDoNumbers');
		Route::match(['get', 'post'], '/sample-do-ready', 'SamplingController@sampleDoReady');
		Route::match(['get', 'post'], '/update-bulk-sample-sale-invoice', 'SamplingController@updateBulkSampleSaleInvoice');
		Route::match(['get', 'post'], '/update-bulk-sample-lr-sale-invoice', 'SamplingController@updateBulkSampleLrSaleInvoice');
		Route::match(['get', 'post'], '/sample-bill-ready', 'SamplingController@sampleBillReady');
		Route::match(['get', 'post'], '/sample-dispatched-material', 'SamplingController@sampleDispatchedMaterial');


		Route::match(['get', 'post'], '/offline-batches', 'OfflineBatchController@offlineBatches');
		Route::match(['get', 'post'], '/add-edit-offline-batch/{id?}', 'OfflineBatchController@addEditOfflineBatch');
		Route::match(['get', 'post'], '/save-offline-batch', 'OfflineBatchController@saveOfflineBatch');
		Route::match(['get', 'post'], '/delete-offline-batch/{id}', 'OfflineBatchController@deleteOfflineBatch');

		Route::match(['get', 'post'], '/voluntary-dispatch/create', 'VoluntaryDispatchController@create');
		Route::match(['get', 'post'], '/voluntary-dispatch/store', 'VoluntaryDispatchController@store');
		Route::match(['get', 'post'], '/voluntary-dispatches', 'VoluntaryDispatchController@list');
		Route::match(['get', 'post'], '/fetch-product-batch-consumptions', 'BatchSheetController@fetchProductBatchConsumptions');


		Route::get('/attendance-report', 'AttendanceReportController@index')->name('attendance.form');
		Route::post('/attendance-report/generate', 'AttendanceReportController@generate')->name('attendance.generate');

		// Attendance screen view + filter
	    Route::get('attendance/view','AttendanceViewController@index');
	    Route::post('attendance/update-status','AttendanceViewController@updateStatus');

	    Route::get('/dvrs', 'UserDvrController@index');
		Route::get('/dvrs/{id}', 'UserDvrController@show');

	});
});
Route::match(['get', 'post'], '/send-notifications', 'Admin\NotificationController@sendNotifications');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/enquiry', 'IndexController@enquiry');
Route::post('/save-quick-enquiry', 'IndexController@saveQuickEnquiry');
Route::post('/save-dealership-enquiry', 'IndexController@saveDealershipEnquiry');
Route::post('/save-job-enquiry', 'IndexController@saveJobEnquiry');
// routes/web.php OR routes/api.php
Route::prefix('/export')->namespace('Export')->group(function(){
	Route::get('dvrs/csv', 'DvrExportController@exportCsv');
});
