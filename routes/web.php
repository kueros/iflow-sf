<?php

use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\ShipperController;
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

Route::get('/webhook/{parametro1}/{parametro2}/{parametro3}/{parametro4}', [Psd_002Controller::class, 'webhook'])->name('psd_002.webhook');
#Route::get('/segundowebhook', [Psd_002Controller::class, 'segundowebhook'])->name('psd_002.segundowebhook');
Route::get('/segundowebhook', [ShopifyController::class, 'segundowebhook'])->name('shopify.segundowebhook');
Route::get('/install', [ShopifyController::class, 'install'])->name('shopify.install');
#Route::get('/carriercreate/{shop}', [ShopifyController::class, 'carrierCreate'])->name('shopify.carriercreate');
Route::get('/carrierCreate', [ShopifyController::class, 'carrierCreate'])->name('shopify.carrierCreate');
Route::get('/carrierList', [ShopifyController::class, 'carrierList'])->name('shopify.carrierList');
Route::get('/carrierMostrar', [ShopifyController::class, 'carrierMostrar'])->name('shopify.carrierMostrar');
Route::get('/carrierDelete', [ShopifyController::class, 'carrierDelete'])->name('shopify.carrierDelete');

#Route::post('/action/{shop}/rates', 'ShipperController@rates');


Route::get('/shopify', [ShopifyController::class, 'index'])->name('shopify.index');
Route::get('/psd1', [Psd_002Controller::class, 'index'])->name('psd1.index');
#Route::get('/psd_002/{parametro1}/{parametro2}/{parametro3}/{parametro4}', [Psd_002Controller::class, 'index'])->name('psd2.index');



Route::delete('/shopify/{id}', [ShopifyController::class, 'destroy'])->name('shopify.destroy');

#Route::get('/shopify/{id}', [ShopifyController::class, 'show'])->name('shopify.view');

Route::get('/shopify/crear', [ShopifyController::class, 'create'])->name('shopify.create');

Route::post('/shopify', [ShopifyController::class, 'store'])->name('shopify.store');

Route::get('/shopify/{id}/editar', [ShopifyController::class, 'edit'])->name('shopify.edit');

Route::put('/shopify/{id}', [ShopifyController::class, 'update'])->name('shopify.update');



