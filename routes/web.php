<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StaffController;
use App\Models\Service;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('front.home', ['services' => Service::where('is_active', true)->get()]);
})->name('home');

Route::post('/newsletter', function (Request $request) {
    $request->validate([
        'email' => 'required|email|unique:subscribers,email',
    ]);

    Subscriber::create([
        'email' => $request->email,
    ]);

    return back()->with('success', 'Inscription réussie !');
})->name('newsletter.subscribe');

/*
|--------------------------------------------------------------------------
| Réservation (booking)
|--------------------------------------------------------------------------
*/
Route::get('/book-appointment', [AppointmentController::class, 'index'])->name('booking.index');

Route::post('/book-appointment', [AppointmentController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('booking.store');

Route::get('/book-appointment/slots', [AppointmentController::class, 'availableSlots'])
    ->middleware('throttle:30,1')
    ->name('booking.slots');

Route::get('/book-appointment/confirmation/{appointment}', [AppointmentController::class, 'confirmation'])
    ->name('booking.confirmation')
    ->middleware('signed');

/*
|--------------------------------------------------------------------------
| Dashboard / Profil (Breeze)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Admin (محمي بـ auth + is_admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('/appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::delete('/appointments/{appointment}', [AdminAppointmentController::class, 'destroy'])->name('appointments.destroy');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
});