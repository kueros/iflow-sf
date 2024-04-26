<?php

use App\Http\Controllers\datoController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\Psd_002Controller;
use App\Models\Dato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
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

Route::get('/test', function (Request $request) {

  $client = new Rest("https://zeusintegra.myshopify.com/admin/api/2024-07/webhooks.json", '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');
  $response = $client->post(
    "webhooks",
    [
      "webhook" => [
        "address" => "pubsub://projectName:topicName",
        "topic" => "customers/update",
        "format" => "json",
      ],
    ]
  );
});



#Route::get('/', [DatoController::class, 'index'])->name('datos.index');

#Route::get('/webhook/{parametro1}/{parametro2}/{parametro3}/{parametro4}', [Psd_002Controller::class, 'webhook'])->name('psd_002.webhook');
Route::get('/webhook/{parametro1}/{parametro2}/{parametro3}/{parametro4}', [Psd_002Controller::class, 'webhook'])->name('psd_002.webhook');
Route::get('/segundowebhook', [Psd_002Controller::class, 'segundowebhook'])->name('psd_002.segundowebhook');


Route::get('/datos', [DatoController::class, 'index'])->name('datos.index');
Route::get('/psd1', [Psd_002Controller::class, 'index'])->name('psd1.index');
#Route::get('/psd_002/{parametro1}/{parametro2}/{parametro3}/{parametro4}', [Psd_002Controller::class, 'index'])->name('psd2.index');



Route::delete('/datos/{id}', [DatoController::class, 'destroy'])->name('datos.destroy');

#Route::get('/datos/{id}', [DatoController::class, 'show'])->name('datos.view');

Route::get('/datos/crear', [DatoController::class, 'create'])->name('datos.create');

Route::post('/datos', [DatoController::class, 'store'])->name('datos.store');

Route::get('/datos/{id}/editar', [DatoController::class, 'edit'])->name('datos.edit');

Route::put('/datos/{id}', [DatoController::class, 'update'])->name('datos.update');
