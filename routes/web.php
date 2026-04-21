<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController; // Tambahkan ini
use App\Http\Controllers\InternController;  // Tambahkan ini
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

// 1. Landing Page
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::post('/locale', function (Request $request) {
    $validated = $request->validate([
        'locale' => 'required|in:id,en',
    ]);

    $request->session()->put('locale', $validated['locale']);

    return back();
})->name('locale.switch');

Route::get('/docs/openapi.yaml', function () {
    $path = base_path('docs/openapi.yaml');

    abort_unless(File::exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/yaml; charset=UTF-8',
    ]);
})->name('docs.openapi');

Route::get('/docs/api-readme', function () {
    $path = base_path('docs/API_README.md');

    abort_unless(File::exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'text/markdown; charset=UTF-8',
    ]);
})->name('docs.api-readme');

Route::get('/docs/frontend-handoff', function () {
    $path = base_path('docs/FRONTEND_API_HANDOFF.md');

    abort_unless(File::exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'text/markdown; charset=UTF-8',
    ]);
})->name('docs.frontend-handoff');

Route::get('/docs/api', function () {
    return view('api-docs');
})->name('docs.api');

Route::get('/docs/swagger', function () {
    return view('swagger-ui');
})->name('docs.swagger');

// 2. Dashboard General (Bawaan Breeze)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. Grup Middleware AUTH (Wajib Login)
Route::middleware('auth')->group(function () {
    
    // Profile Management (Bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- FITUR COMPANY / MITRA (SESUAI GAMBAR 1) ---
    Route::prefix('company')->group(function () {
        // Dashboard Stats & Recent Applicants
        Route::get('/dashboard', [CompanyController::class, 'getDashboardData'])
            ->name('company.dashboard');
            
        // Manajemen Lowongan
        Route::get('/jobs', [CompanyController::class, 'getJobs'])
            ->name('company.jobs');
        Route::post('/jobs', [CompanyController::class, 'storeJob'])
            ->name('company.jobs.store');
    });

    // --- FITUR INTERN / PELAMAR ---
    Route::prefix('intern')->group(function () {
        Route::get('/applications', [InternController::class, 'getMyApplications'])
            ->name('intern.applications');
    });
});

require __DIR__.'/auth.php';
