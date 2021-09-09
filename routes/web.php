<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\InvoicesDetailsController;
use App\Http\Controllers\invoiceAttachmentsController;
use App\Http\Controllers\InvoiceAchiveController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Invoices_ReportController;
use App\Http\Controllers\Customers_ReportController;





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
Auth::routes(['register' => false]);


Route::get('/', function () {
    return view('auth.login');
});




Route::get('/Invoice_Paid',[InvoicesController::class,'Invoice_Paid'])->name('Invoice_Paid');

Route::get('Invoice_UnPaid',[InvoicesController::class,'Invoice_UnPaid']);

Route::get('Invoice_Partial',[InvoicesController::class,'Invoice_Partial']);
Route::get('export_invoices', [InvoicesController::class,'export']);


Route::group(['middleware' => ['auth']], function() {
    Route::resource('users',UserController::class);
    Route::resource('roles',RoleController::class);
});


Route::get('invoices_report', [Invoices_ReportController::class,'index']);

Route::post('Search_invoices', [Invoices_ReportController::class,'Search_invoices']);

Route::get('customers_report', [Customers_ReportController::class,'index'])->name("customers_report");

Route::post('Search_customers', [Customers_ReportController::class,'Search_customers']);


Route::resource('invoices', InvoicesController::class);
Route::resource('sections', SectionsController::class);
Route::resource('invoiceAttachments', invoiceAttachmentsController::class);
Route::resource('Archive', InvoiceAchiveController::class);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::resource('deletlis', InvoicesDetailsController::class);


Route::resource('products', ProductsController::class);

Route::get('/section/{id}', [InvoicesController::class,'getproducts']);

Route::get('/invoicesDetails/{id}', [InvoicesDetailsController::class,'edit'])->name('getinvoices');

Route::get('download/{invoice_number}/{file_name}', [InvoicesDetailsController::class,'get_file']);

Route::get('view_file/{invoice_number}/{file_name}', [InvoicesDetailsController::class,'open_file'])->name('view_file');

Route::post('delete_file', [InvoicesDetailsController::class,'destroy'])->name('delete_file');


Route::get('/{page}', [AdminController::class,'index']);


Route::get('Print_invoice/{id}',[InvoicesController::class,'Print_invoice']);


Route::get('/edit_invoice/{id}', [InvoicesController::class,'edit']);

Route::get('/Status_show/{id}', [InvoicesController::class,'show']);

Route::post('Status_Update/{id}', [InvoicesController::class,'Status_Update']);


Route::get('MarkAsRead_all',[InvoicesController::class,'MarkAsRead_all'])->name('MarkAsRead_all');




Route::get('unreadNotifications_count', [InvoicesController::class,'unreadNotifications_count'])->name('unreadNotifications_count');

Route::get('unreadNotifications', [InvoicesController::class,'unreadNotifications'])->name('unreadNotifications');


Route::post('/invoices/update/{id}', [InvoicesController::class,'update']);


   
   



