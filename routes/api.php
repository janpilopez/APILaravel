<?php

use App\Http\Controllers\Apiv2\CategoriaController;
use App\Http\Controllers\Apiv2\CompraController;
use App\Http\Controllers\Apiv2\MarcaController;
use App\Http\Controllers\Apiv2\ProductoController;
use App\Http\Controllers\ClienteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::get('/clientes', [ClienteController::class, 'index']);
Route::apiResource('clientes', ClienteController::class);

Route::apiResource('marcas', MarcaController::class);
Route::get('marcas/{marca}/productos', [MarcaController::class, 'productosPorMarca']);

Route::apiResource('categorias', CategoriaController::class);
Route::get('categorias/{categoria}/productos', [CategoriaController::class, 'productosPorCategoria']);

Route::apiResource('productos', ProductoController::class);

Route::apiResource('compras', CompraController::class);
