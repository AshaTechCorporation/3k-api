<?php

use App\Http\Controllers\AreaCompanyController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BrandModelController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\CategoryAttributeController;
use App\Http\Controllers\CategoryProductController;
use App\Http\Controllers\CCController;
use App\Http\Controllers\CleamHistoryController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ConfigTimeController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DeductTypeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\IncomeDeductTransController;
use App\Http\Controllers\IncomeTypeController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\KhetController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PaymentPeriodController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProductAttributeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SubCategoryProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TimeAttendanceController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\PromotionListController;
use App\Http\Controllers\WorkTypeController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\CheckListController;
use App\Http\Controllers\IncomeExpensesTrackerTypeController;
use App\Http\Controllers\IncomeExpensesTrackerController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\DebtorAccountsController;
use App\Http\Controllers\CreditorAccountsController;
use App\Http\Controllers\ArApController;
use App\Http\Controllers\ProductAttributeTransController;
use Illuminate\Support\Facades\Route;

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

//////////////////////////////////////////web no route group/////////////////////////////////////////////////////
//Login Admin
Route::post('/login', [LoginController::class, 'login']);

Route::post('/check_login', [LoginController::class, 'checkLogin']);

//user
Route::post('/create_admin', [UserController::class, 'createUserAdmin']);
Route::post('/forgot_password_user', [UserController::class, 'ForgotPasswordUser']);

// Category Product
Route::resource('category_product', CategoryProductController::class);
Route::post('/category_product_page', [CategoryProductController::class, 'getPage']);
Route::get('/get_category_product', [CategoryProductController::class, 'getList']);

// Category Product Attribute
Route::resource('category_attribute', CategoryAttributeController::class);
Route::post('/category_attribute_page', [CategoryAttributeController::class, 'getPage']);
Route::get('/get_category_attribute', [CategoryAttributeController::class, 'getList']);

// area
Route::resource('area', AreaController::class);
Route::post('/area_page', [AreaController::class, 'getPage']);
Route::get('/get_area', [AreaController::class, 'getList']);
Route::post('/update_area', [AreaController::class, 'updateData']);

// comp
Route::resource('companie', CompanyController::class);
Route::post('/companie_page', [CompanyController::class, 'getPage']);
Route::get('/get_companie', [CompanyController::class, 'getList']);
Route::post('/update_companie', [CompanyController::class, 'updateData']);

// finance
Route::resource('finance', FinanceController::class);
Route::post('/finance_page', [FinanceController::class, 'getPage']);
Route::get('/get_finance', [FinanceController::class, 'getList']);
Route::post('/update_finance', [FinanceController::class, 'updateData']);


// insurance
Route::resource('insurance', InsuranceController::class);
Route::post('/insurance_page', [InsuranceController::class, 'getPage']);
Route::get('/get_insurance', [InsuranceController::class, 'getList']);
Route::post('/update_insurance', [InsuranceController::class, 'updateData']);

// broker
Route::resource('broker', BrokerController::class);
Route::post('/broker_page', [BrokerController::class, 'getPage']);
Route::get('/get_broker', [BrokerController::class, 'getList']);
Route::post('/update_broker', [BrokerController::class, 'updateData']);


// Product
Route::resource('product', ProductController::class);
Route::post('/product_page', [ProductController::class, 'getPage']);
Route::get('/get_product_by_brand/{id}', [ProductController::class, 'getListByBrand']);
Route::get('/get_product_by_model/{id}', [ProductController::class, 'getListByModel']);
Route::post('/update_product', [ProductController::class, 'updateData']);
Route::get('/get_product_all', [ProductController::class, 'getListAll']);

// Product Attribute
Route::resource('product_attribute', ProductAttributeController::class);
Route::post('/product_attribute_page', [ProductAttributeController::class, 'getPage']);
Route::get('/get_product_attribute/{id}', [ProductAttributeController::class, 'getList']);
Route::post('/update_product_attribute', [ProductAttributeController::class, 'updateData']);
Route::get('/get_product_attribute_all', [ProductAttributeController::class, 'getListAll']);

// Brand
Route::resource('brand', BrandController::class);
Route::post('/brand_page', [BrandController::class, 'getPage']);
Route::get('/get_brand', [BrandController::class, 'getList']);
Route::post('/update_brand', [BrandController::class, 'updateData']);
Route::get('/get_brand_count', [BrandController::class, 'getListCount']);

// Brand Model
Route::resource('brand_model', BrandModelController::class);
Route::post('/brand_model_page', [BrandModelController::class, 'getPage']);
Route::get('/get_brand_model/{id}', [BrandModelController::class, 'getList']);
Route::get('/get_brand_model_all', [BrandModelController::class, 'getListAll']);
Route::post('/update_brand_model', [BrandModelController::class, 'updateData']);
Route::get('/get_brand_model_count/{id}', [BrandModelController::class, 'getListCount']);

// CC
Route::resource('c_c', CCController::class);
Route::post('/c_c_page', [CCController::class, 'getPage']);
Route::get('/get_c_c', [CCController::class, 'getList']);

// Color
Route::resource('color', ColorController::class);
Route::post('/color_page', [ColorController::class, 'getPage']);
Route::get('/get_color', [ColorController::class, 'getList']);


// Department
Route::resource('department', DepartmentController::class);
Route::post('/department_page', [DepartmentController::class, 'getPage']);
Route::get('/get_department', [DepartmentController::class, 'getList']);

// Postion
Route::resource('position', PositionController::class);
Route::post('/position_page', [PositionController::class, 'getPage']);
Route::get('/get_position', [PositionController::class, 'getList']);

// Supplier
Route::resource('supplier', SupplierController::class);
Route::post('/supplier_page', [SupplierController::class, 'getPage']);
Route::get('/get_supplier', [SupplierController::class, 'getList']);

// Cleam History
Route::resource('cleam', CleamHistoryController::class);
Route::post('/cleam_page', [CleamHistoryController::class, 'getPage']);
Route::get('/get_cleam/{id}', [CleamHistoryController::class, 'getList']);

// Time
Route::resource('time', TimeAttendanceController::class);
Route::post('/import_time', [TimeAttendanceController::class, 'Import']);
Route::post('/time_page', [TimeAttendanceController::class, 'getPage']);
Route::get('/get_time/{month}/{year}', [TimeAttendanceController::class, 'getList']);
Route::post('/get_time_check', [TimeAttendanceController::class, 'getTimeCheck']);

// Payment
Route::resource('payment_period', PaymentPeriodController::class);
Route::post('/payment_period_page', [PaymentPeriodController::class, 'getPage']);
Route::get('/get_payment_period/{id}', [PaymentPeriodController::class, 'getList']);
Route::post('/update_payment_period', [PaymentPeriodController::class, 'updateData']);

// Permission
Route::resource('permission', PermissionController::class);
Route::post('/permission_page', [PermissionController::class, 'getPage']);
Route::get('/get_permission', [PermissionController::class, 'getList']);
Route::post('/get_permisson_menu', [PermissionController::class, 'getPermissonMenu']);

// Transfer
Route::resource('transfer', TransferController::class);
Route::post('/transfer_page', [TransferController::class, 'getPage']);
Route::get('/get_transfer', [TransferController::class, 'getList']);
Route::post('/update_status_transfer', [TransferController::class, 'updateStatus']);

// purchase_order
Route::resource('purchase_order', PurchaseOrderController::class);
Route::post('/purchase_order_page', [PurchaseOrderController::class, 'getPage']);
Route::get('/get_purchase_order', [PurchaseOrderController::class, 'getList']);
Route::post('/update_status_purchase_order', [PurchaseOrderController::class, 'updateStatus']);

// income type
Route::resource('income', IncomeTypeController::class);
Route::post('/income_page', [IncomeTypeController::class, 'getPage']);
Route::get('/get_income', [IncomeTypeController::class, 'getList']);

// deduct type
Route::resource('deduct', DeductTypeController::class);
Route::post('/deduct_page', [DeductTypeController::class, 'getPage']);
Route::get('/get_deduct', [DeductTypeController::class, 'getList']);

// config time
Route::resource('config_late', ConfigTimeController::class);
Route::post('/config_late_page', [ConfigTimeController::class, 'getPage']);
Route::get('/get_config_late', [ConfigTimeController::class, 'getList']);

// payroll
Route::resource('payroll', PayrollController::class);
Route::post('/payroll_page', [PayrollController::class, 'getPage']);
Route::get('/get_payroll', [PayrollController::class, 'getList']);
Route::post('/payroll_calculate', [PayrollController::class, 'payroll']);

// area companies
Route::resource('area_companie', AreaCompanyController::class);
Route::post('/area_companie_page', [AreaCompanyController::class, 'getPage']);
Route::get('/get_area_companie', [AreaCompanyController::class, 'getList']);

// promotion
Route::resource('promotion', PromotionController::class);
Route::post('/promotion_page', [PromotionController::class, 'getPage']);
Route::get('/get_promotion', [PromotionController::class, 'getList']);

// garage
Route::resource('garage', GarageController::class);
Route::post('/garage_page', [GarageController::class, 'getPage']);
Route::get('/get_garage', [GarageController::class, 'getList']);

// //Menu
// Route::resource('menu', MenuController::class);
// Route::get('/get_menu', [MenuController::class, 'getList']);

// //Menu Permission
// Route::resource('menu_permission', MenuPermissionController::class);
// Route::get('/get_menu_permission', [BannerController::class, 'getList']);
// Route::post('checkAll', [MenuPermissionController::class, 'checkAll']);

//controller
Route::post('upload_images', [Controller::class, 'uploadImages']);
// Route::post('upload_file', [Controller::class, 'uploadFile']);

//user
Route::resource('user', UserController::class);
Route::get('/get_user', [UserController::class, 'getList']);
Route::post('/user_page', [UserController::class, 'getPage']);
Route::get('/user_profile', [UserController::class, 'getProfileUser']);
Route::post('/update_user', [UserController::class, 'update']);
Route::get('/get_user_by_department/{id}', [UserController::class, 'getUserByDep']);
Route::put('/reset_password_user/{id}', [UserController::class, 'ResetPasswordUser']);
Route::post('/update_profile_user', [UserController::class, 'updateProfileUser']);
Route::get('/get_profile_user', [UserController::class, 'getProfileUser']);

// income expenses type
Route::resource('income_expense_type', IncomeExpensesTrackerTypeController::class);
Route::post('/income_expense_type_page', [IncomeExpensesTrackerTypeController::class, 'getPage']);
Route::get('/get_income_expense_type', [IncomeExpensesTrackerTypeController::class, 'getList']);
    
// income deduct trans
Route::resource('income_expense_tracker', IncomeExpensesTrackerController::class);
Route::post('/income_expense_tracker_page', [IncomeExpensesTrackerController::class, 'getPage']);
Route::get('/get_income_expense_tracker/{date}', [IncomeExpensesTrackerController::class, 'getList']);

// transactions
Route::post('/transactions', [TransactionsController::class, 'store']);
Route::put('/transactions/{id}', [TransactionsController::class, 'update']);
Route::post('/transactions_page', [TransactionsController::class, 'getPage']);
Route::get('/get_transactions/{date?}', [TransactionsController::class, 'getList']);
Route::get('/transactions/{id}', [TransactionsController::class, 'show']);

// debtors
Route::get('/debtors', [DebtorAccountsController::class, 'index']);
Route::post('/debtors', [DebtorAccountsController::class, 'store']);
Route::post('/debtors/{id}/pay', [DebtorAccountsController::class, 'pay']);
Route::post('/debtors/pay', [DebtorAccountsController::class, 'payBulk']);
Route::post('/debtors_page', [DebtorAccountsController::class, 'getPage']);
Route::get('/get_debtors', [DebtorAccountsController::class, 'getList']);
Route::get('/debtors/{id}', [DebtorAccountsController::class, 'show']);

// creditors
Route::get('/creditors', [CreditorAccountsController::class, 'index']);
Route::post('/creditors', [CreditorAccountsController::class, 'store']);
Route::post('/creditors/pay', [CreditorAccountsController::class, 'pay']);
Route::post('/creditors_page', [CreditorAccountsController::class, 'getPage']);
Route::get('/get_creditors', [CreditorAccountsController::class, 'getList']);
Route::get('/creditors/{id}', [CreditorAccountsController::class, 'show']);

Route::put('/update_password_user/{id}', [UserController::class, 'updatePasswordUser']);
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => 'checkjwt'], function () {
    // Client
    Route::resource('client', ClientsController::class);
    Route::post('/client_page', [ClientsController::class, 'getPage']);
    Route::get('/get_client', [ClientsController::class, 'getList']);
    Route::post('/update_client', [ClientsController::class, 'updateData']);

    // Order
    Route::resource('orders', OrdersController::class);
    Route::post('/orders_page', [OrdersController::class, 'getPage']);
    Route::get('/get_orders', [OrdersController::class, 'getList']);
    Route::post('/update_status_orders', [OrdersController::class, 'updateStatus']);

    Route::post('/user_delete_all', [UserController::class, 'destroy_all']);

    // income deduct trans
    Route::resource('income_deduct_trans', IncomeDeductTransController::class);
    Route::post('/income_deduct_trans_page', [IncomeDeductTransController::class, 'getPage']);
    Route::get('/get_income_deduct_trans/{userid}/{month}', [IncomeDeductTransController::class, 'getList']);

    Route::resource('check_lists', CheckListController::class);

    Route::put('/update_status_jobs_work_type/{id}', [JobsController::class, 'updateStepJobTypeListStatus']);

    Route::get('/get_master_jobs', [JobsController::class, 'getMasterList']);

    // ar ap
    Route::resource('ar_ap', ArApController::class);

    Route::put('/update_status_ar_ap/{id}', [ArApController::class, 'updateStatus']);

});

Route::get('/export_pdf_payroll/{id}', [Controller::class, 'pay_slip']);

//upload

Route::post('/upload_file', [UploadController::class, 'uploadFile']);

//export pdf excel word
Route::get('/excel_payslip', [FileController::class, 'excel_payslip']);
Route::get('/pdf_payslip', [FileController::class, 'pdf_payslip']);
Route::post('/downloadExcel', [FileController::class, 'downloadExcel']);

Route::post('/import_data', [ProductController::class, 'Import']);


// Member
Route::resource('member', MemberController::class);
Route::post('/member_page', [MemberController::class, 'getPage']);
Route::get('/get_member', [MemberController::class, 'getList']);

// Member
Route::resource('khet', KhetController::class);
Route::post('/khet_page', [KhetController::class, 'getPage']);
Route::get('/get_khet', [KhetController::class, 'getList']);

// Member
Route::resource('province', ProvinceController::class);
Route::post('/province_page', [ProvinceController::class, 'getPage']);
Route::get('/get_province', [ProvinceController::class, 'getList']);

Route::get('/member_excel/{id}', [MemberController::class, 'excel_export']);

// Discount
Route::resource('discount', DiscountController::class);
Route::post('/discount_page', [DiscountController::class, 'getPage']);
Route::get('/get_discount', [DiscountController::class, 'getList']);

// Promotion List
Route::resource('promotion_list', PromotionListController::class);
Route::post('/promotion_list_page', [PromotionListController::class, 'getPage']);
Route::get('/get_promotion_list', [PromotionListController::class, 'getList']);

Route::post('/upload-image', [Controller::class, 'uploadImageDropzone']);
Route::put('/updage_image_seq/{id}', [ProductController::class, 'updateImageSeq']);

// Expense
Route::resource('expenses', ExpenseTypeController::class);
Route::post('/expenses_page', [ExpenseTypeController::class, 'getPage']);
Route::get('/get_expenses', [ExpenseTypeController::class, 'getList']);

// WorkType
Route::resource('work_type', WorkTypeController::class);
Route::post('/work_type_page', [WorkTypeController::class, 'getPage']);
Route::get('/get_work_type', [WorkTypeController::class, 'getList']);

// Jobs
Route::resource('jobs', JobsController::class);
Route::post('/jobs_page', [JobsController::class, 'getPage']);
Route::get('/get_jobs', [JobsController::class, 'getList']);

// Check List

Route::post('/check_lists_page', [CheckListController::class, 'getPage']);
Route::get('/get_check_lists', [CheckListController::class, 'getList']);

// report summary expense
Route::post('/report_summary_by_product', [JobsController::class, 'getAllProductExpenseSummaryWithDetails']);

// ar ap
Route::post('/ar_ap_page', [ArApController::class, 'getPage']);
Route::get('/get_ar_ap/{date}', [ArApController::class, 'getList']);

Route::get('/dashboard', [Controller::class, 'getMockupDashboard']);

// product attribute trans
Route::resource('product_attribute_trans', ProductAttributeTransController::class);
Route::post('/product_attribute_trans_page', [ProductAttributeTransController::class, 'getPage']);
Route::get('/get_product_attribute_trans', [ProductAttributeTransController::class, 'getList']);

Route::put('update_status_product_attribute_trans/{id}', [ProductAttributeTransController::class, 'updateStatus']);


