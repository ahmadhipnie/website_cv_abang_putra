<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\MobileController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::post('/login', [MobileController::class, 'login']);
Route::get('/getAllKategori', [MobileController::class, 'getAllKategori']);
Route::get('/getAllBarang', [MobileController::class, 'getAllBarang']);
Route::post('/getImagesBarangByIdBarang', [MobileController::class, 'getImagesBarangByIdBarang']);
Route::post('/getBarangsByKategori', [MobileController::class, 'getBarangsByKategori']);
Route::post('/getBarangsBySearch', [MobileController::class, 'getBarangsBySearch']);
Route::post('/getBarangsBySearchAdmin', [MobileController::class, 'getBarangsBySearchAdmin']);
Route::post('/getPromosBySearchAdmin', [MobileController::class, 'getPromosBySearchAdmin']);
Route::get('/getAllGambarPromo', [MobileController::class, 'getAllGambarPromo']);
Route::post('/getImagesPromoByIdPromo', [MobileController::class, 'getImagesPromoByIdPromo']);
Route::post('/kirimFeedbackReseller', [MobileController::class, 'kirimFeedbackReseller']);
Route::get('/getAllFeedback', [MobileController::class, 'getAllFeedback']);
Route::get('/getAllPromo', [MobileController::class, 'getAllPromo']);
Route::post('/updateUserReseller', [MobileController::class, 'updateUserReseller']);
Route::post('/deletePromo', [MobileController::class, 'deletePromo']);
Route::post('/deleteBarang', [MobileController::class, 'deleteBarang']);
Route::post('/updateStokBarang', [MobileController::class, 'updateStokBarang']);
Route::post('/addKategori', [MobileController::class, 'addKategori']);
Route::post('/updatePassword', [MobileController::class, 'updatePassword']);
Route::post('/addUserAndReseller', [MobileController::class, 'addUserAndReseller']);
Route::post('/addBarang', [MobileController::class, 'addBarang']);
Route::post('/addPromo', [MobileController::class, 'addPromo']);

Route::post('/deleteKategoriWithCheck', [MobileController::class, 'deleteKategoriWithCheck']);
