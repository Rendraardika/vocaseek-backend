<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// --- AUTH CONTROLLERS ---
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ApiEmailVerificationController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LanguagePreferenceController;

// --- APP CONTROLLERS ---
use App\Http\Controllers\InternController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TalentController;

// --- ADMIN CONTROLLERS ---
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminTalentController;
use App\Http\Controllers\Auth\AdminPartnerController;
use App\Http\Controllers\Auth\AdminInvitationController;
use App\Http\Controllers\Auth\AdminUserController;
use App\Http\Controllers\Auth\AdminVerificationController;
use App\Http\Controllers\Auth\AdminProfileController;


Route::get('/landing-stats', [CompanyController::class, 'getPublicStats']);
Route::get('/popular-vacancies', [CompanyController::class, 'getPublicJobs']);
Route::get('/partners', [CompanyController::class, 'getPublicPartners']);

// Auth Utama
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::get('/admin/invitations/verify', [AdminInvitationController::class, 'verify'])
    ->middleware('throttle:12,1');
Route::post('/admin/invitations/accept', [AdminInvitationController::class, 'accept'])
    ->middleware('throttle:12,1');
Route::get('/email/verify/{id}/{hash}', [ApiEmailVerificationController::class, 'verify'])
    ->middleware('throttle:20,1')
    ->name('api.verification.verify');
Route::get('/email/pending-verify/{id}/{hash}', [ApiEmailVerificationController::class, 'verifyPending'])
    ->middleware('throttle:20,1')
    ->name('api.pending-registration.verify');
Route::post('/email/verification-notification', [ApiEmailVerificationController::class, 'resend'])
    ->middleware('throttle:10,1');

// Social Auth & Passwords
Route::get('/auth/google', function () {
    return redirect()->route('google.redirect');
});

Route::get('/auth/google/callback', function () {
    return redirect()->route('google.callback', request()->query());
});

Route::post('/auth/google/token', [GoogleController::class, 'loginWithGoogleToken'])
    ->withoutMiddleware([
        EnsureFrontendRequestsAreStateful::class,
        VerifyCsrfToken::class,
    ]);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/forgot-password/validate-token', [ForgotPasswordController::class, 'validateResetToken']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

// Test Connection
Route::get('/test', function () {
    return response()->json(['message' => 'API Vocaseek Aktif & Terhubung']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/language', [LanguagePreferenceController::class, 'show']);
    Route::put('/language', [LanguagePreferenceController::class, 'update']);
    Route::get('/preferences/language', [LanguagePreferenceController::class, 'show']);
    Route::put('/preferences/language', [LanguagePreferenceController::class, 'update']);

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    
    Route::prefix('intern')->middleware('role:intern')->group(function () {
        Route::get('/profile', [InternController::class, 'getProfile']);
        Route::get('/test/questions', [InternController::class, 'getTestQuestions']);
        Route::post('/update-profile', [InternController::class, 'updateProfile']);
        Route::put('/update-profile', [InternController::class, 'updateProfile']);
        Route::post('/start-test', [InternController::class, 'startTest']);
        Route::post('/submit-test', [InternController::class, 'submitPreTest']);
        Route::post('/apply', [InternController::class, 'applyJob']);
        Route::get('/applications', [InternController::class, 'getMyApplications']);
    });

    
    Route::prefix('company')->middleware('role:company')->group(function () {
        // Profil & Dashboard
        Route::get('/profile', [CompanyController::class, 'getCompanyProfile']); 
        Route::post('/profile/update', [CompanyController::class, 'updateProfile']); 
        Route::get('/dashboard', [CompanyController::class, 'getDashboardData']);
        
        // Fitur Seleksi Pelamar
        Route::get('/jobs/{jobId}/applicants', [CompanyController::class, 'getApplicantsByJob']);
        Route::put('/applications/{id}/status', [CompanyController::class, 'updateApplicationStatus']);

        // CRUD Lowongan (Satu pintu lewat CompanyController)
        Route::get('/jobs', [CompanyController::class, 'getJobPostings']);
        Route::post('/jobs', [CompanyController::class, 'storeJob']);
        Route::put('/jobs/{id}', [CompanyController::class, 'updateJob']); 
        Route::delete('/jobs/{id}', [CompanyController::class, 'destroyJob']);

        // Management Talent (Database Kandidat)
        Route::get('/talent/candidates', [TalentController::class, 'getAllCandidates']);
        Route::get('/talent/candidates/{id}/detail', [TalentController::class, 'getCandidateDetail']);
        Route::post('/talent/candidates/manual', [TalentController::class, 'storeManualCandidate']);
        Route::put('/talent/candidates/{id}/status', [TalentController::class, 'updateCandidateStatus']);
        Route::get('/talent/selected', [TalentController::class, 'getSelectedCandidates']);
    });

    
    Route::prefix('admin')->group(function () {
        
        // Area Bersama (Admin & Staff)
        Route::middleware('role:super_admin,staff_admin')->group(function () {
            
            Route::prefix('profile')->group(function () {
                Route::get('/', [AdminProfileController::class, 'show']);
                Route::post('/update', [AdminProfileController::class, 'update']);
                Route::post('/change-password', [AdminProfileController::class, 'changePassword']);
                Route::put('/change-password', [AdminProfileController::class, 'changePassword']);
            });

            Route::get('/overview', [AdminDashboardController::class, 'getOverview']);
            Route::get('/talents', [AdminTalentController::class, 'index']);
            Route::get('/talents/{id}', [AdminTalentController::class, 'show']);
            Route::get('/talents/{id}/download-cv', [AdminTalentController::class, 'downloadCv']);
            Route::get('/partners', [AdminPartnerController::class, 'index']);
            Route::get('/partners/{id}', [AdminPartnerController::class, 'show']); 
            
            Route::prefix('verification')->group(function () {
                Route::get('/', [AdminVerificationController::class, 'index']); 
                Route::put('/{id}/review-status', [AdminVerificationController::class, 'updateReviewStatus']); 
                Route::get('/{id}/detail', [AdminVerificationController::class, 'show']);
            });
        });

        // Area Khusus
        Route::middleware('role:super_admin')->group(function () {
            Route::prefix('users-management')->group(function () {
                Route::get('/', [AdminUserController::class, 'index']);      
                Route::put('/{id}/status', [AdminUserController::class, 'updateStatus']); 
                Route::delete('/{id}', [AdminUserController::class, 'destroy']);
            });

            Route::prefix('users')->group(function () {
                Route::post('/invite', [AdminUserController::class, 'invite']);
            });

            Route::prefix('invitations')->group(function () {
                Route::post('/resend', [AdminInvitationController::class, 'resend']);
                Route::post('/cancel', [AdminInvitationController::class, 'cancel']);
            });

            Route::post('/partners', [AdminPartnerController::class, 'store']); 
            Route::delete('/partners/{id}', [AdminPartnerController::class, 'destroy']);
            Route::delete('/talents/{id}', [AdminTalentController::class, 'destroy']);
            Route::post('/verification/{id}/final', [AdminVerificationController::class, 'finalVerification']);
        });
    });
});
