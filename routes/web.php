<?php

use App\Http\Controllers\CinemaController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');
Route::get('/detail', function () {
    return view('schedule.detail-film');
})->name('schedule-detail');
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::get('/signup', function () {
    return view('auth.signup');
})->name('signup');
// http method
// 1. get : buat nampilin halamaan 
// 2. post : menambahkan data baru
// 3. patch : mengubah data
// 3. delete : menghapus data
Route::post('/signup', [UserController::class, 'register'])
->name('signup.register');
Route::post('/login', [UserController::class, 'loginAuth'])
->name('login.auth');
Route::get('/logout', [UserController::class, 'logout'])
->name('logout');

// halaman khusus admin
// middleware() : memanggil middleware yang akan digunakan
// group : mengelompokkan route agar mengikuti sifat sebelumnya (sebelumnya = middleware)
// prefix() : awalan path, agar /admin ditulis 1 kali tapi bisa digunakan berkali kali
Route::middleware('isAdmin')->prefix('/admin')->name('admin.')->group(function() {
    // admin dashboard disimpan di group middleware agar dapat menggunakan middleware tsb. 
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // data film
    Route::prefix('/cinemas')->name('cinemas.')->group(function(){
        Route::get('/', [CinemaController::class, 'index'])
        ->name('index');    
        Route::get('/create', [CinemaController::class, 'create'])
        ->name('create');
        Route::post('/store', [CinemaController::class, 'store'])
        ->name('store');
        // {id} : parameter placeholder, mengirim data ke controller. digunakan ketika akan mengambil data spesifik
        Route::get('/edit/{id}', [CinemaController::class, 'edit'])
        ->name('edit');
        Route::put('/update/{id}', [CinemaController::class, 'update'])
        ->name('update');
        Route::delete('/delete/{id}', [CinemaController::class, 'destroy'])
        ->name('delete');
        Route::get('/export', [CinemaController::class, 'exportExcel'])
        ->name('export');
    });

    //data Pengguna admin dan staff
    Route::prefix('/user')->name('users.')->group(function() {
        Route::get('/', [UserController::class, 'index'])
        ->name('index');
        Route::get('/create', [UserController::class, 'create'])
        ->name('create');
        Route::post('/store', [UserController::class, 'store'])
        ->name('store');
        Route::get('/edit/{id}', [UserController::class, 'edit'])
        ->name('edit');
        Route::put('/update/{id}', [UserController::class, 'update'])
        ->name('update');
        Route::delete('/delete/{id}', [UserController::class, 'destroy'])
        ->name('delete');
        Route::get('/export', [UserController::class, 'exportExcel'])
        ->name('export');
    });
    Route::prefix('/movies')->name('movies.')->group(function() {
        Route::get('/', [MovieController::class, 'index'])->name('index');
        Route::get('/create', [MovieController::class, 'create'])->name('create');
        Route::post('/store', [MovieController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [MovieController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [MovieController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [MovieController::class, 'destroy'])->name('delete');
        Route::patch('/toggle/{id}', [MovieController::class, 'toggle'])->name('toggle');
        Route::get('/export', [MovieController::class, 'exportExcel'])->name('export');
    });
});

// beranda 
Route::get('/', [MovieController::class, 'home'])->name('home');
Route::get('/movies/active', [MovieController::class, 'homeMovies'])->name('home.movies.active');
// detail film
Route::get('/detail/{id}', [MovieController::class, 'detail'])->name('detail');


Route::middleware('isStaff')->prefix('/staff')->name('staff.')->group(function() {
    Route::get('/', function() {
        return view('staff.dashboard');
    })->name('dashboard');

    Route::prefix('/promos')->name('promos.')->group(function() {
        Route::get('/', [PromoController::class, 'index'])->name('index');
        Route::get('/create', [PromoController::class, 'create'])->name('create');
        Route::post('/store', [PromoController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [PromoController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [PromoController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [PromoController::class, 'destroy'])->name('delete');
        Route::get('/export', [PromoController::class, 'exportExcel'])->name('export');
    });
});