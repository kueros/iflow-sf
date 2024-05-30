<?php

use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\ShopifyAPI;
use App\Http\Controllers\CarrierServiceController;
use App\Http\Controllers\WebhookServiceController;
use App\Http\Controllers\Psd_002Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Shopify\Clients\Rest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', [ShopifyController::class, 'create'])->name('shopify.create');

Route::get('/error', function () {
    return view('error');
});

Route::get('/error_configs', function () {
    return view('error_configs');
});

#Route::get('/error', [ShopifyController::class, 'create'])->name('shopify.create');

Route::get('/shopify', [ShopifyController::class, 'index'])->name('shopify.index');
Route::get('/install', [ShopifyController::class, 'install'])->name('shopify.install');
Route::get('/segundowebhook', [ShopifyController::class, 'segundowebhook'])->name('shopify.segundowebhook');

Route::get('/carrierList', [ShopifyController::class, 'carrierList'])->name('ShopifyController.carrierList');
Route::get('/carrierCreate', [ShopifyController::class, 'carrierCreate'])->name('ShopifyController.carrierCreate');
Route::get('/carrierShow/{carrierId}', [ShopifyController::class, 'carrierShow'])->name('ShopifyController.carrierShow');
Route::get('/carrierDelete/{carrierId}', [ShopifyController::class, 'carrierDelete'])->name('ShopifyController.carrierDelete');

Route::get('/webhookCreate', [ShopifyController::class, 'webhookCreate'])->name('ShopifyController.webhookCreate');
Route::get('/webhookDelete/{webhookId}', [ShopifyController::class, 'webhookDelete'])->name('ShopifyController.webhookDelete');
Route::get('/webhookList', [ShopifyController::class, 'webhookList'])->name('ShopifyController.webhookList');
Route::get('/webhookShow/{webhookId}', [ShopifyController::class, 'webhookShow'])->name('ShopifyController.webhookShow');
Route::get('/webhookCreateOrdersPaid', [ShopifyController::class, 'webhookCreateOrdersPaid'])->name('ShopifyController.webhookCreateOrdersPaid');
Route::get('/webhookCreateOrdersCancelled', [ShopifyController::class, 'webhookCreateOrdersCancelled'])->name('ShopifyController.webhookCreateOrdersCancelled');

Route::delete('/shopify/{id}', [ShopifyController::class, 'destroy'])->name('shopify.destroy');
Route::post('/shopify', [ShopifyController::class, 'store'])->name('shopify.store');
Route::get('/shopify/{id}/editar', [ShopifyController::class, 'edit'])->name('shopify.edit');
Route::put('/shopify/{id}', [ShopifyController::class, 'update'])->name('shopify.update');


