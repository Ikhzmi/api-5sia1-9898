<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//  default endpoint API: http://api-5sia1.test/api
/**
 * API Resource untuk model Product
 */
// 1. Ambil semua data Produk beserta pemiliknya (user)
// action url = [NamaController::class, 'method']
Route::get('/products/semuanya', [ProductController::class, 'index']);
// 2. Cari produk tersedia berdasarkan nama
Route::get('/products/cari', [ProductController::class, 'search']);
// 3. Ambil Produk berdasarkan id
Route::get('/product/{id}', [ProductController::class, 'show']);
// 4. Tambah Produk
Route::post('/product/tambah', [ProductController::class, 'store']);
// 5. Ubah Produk
Route::put('/product/ubah/{id}', [ProductController::class, 'update']);
// 6. Hapus Produk
Route::delete('/product/hapus/{id}', [ProductController::class, 'destroy']);


/**
 * API Resource untuk model User
 */
// route ambil semua data user
// method: GET
Route::get('/users', [UserController::class,'index']);
// route cari user berdasarkan id
// method: Get
Route::get('/user/find', [UserController::class,'show']);

// route cari user berdasarkan kemiripan nama atau email
// method: Get
Route::get('/user/search', [UserController::class,'search']);

// Registrasi User
Route::post('/register', [UserController::class,'store']);

// Ubah Data User
Route::put('/user/edit/{user}',[UserController::class,'update']);

// Hapus Data User
Route::delete('/user/delete', [UserController::class,'destroy']);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');