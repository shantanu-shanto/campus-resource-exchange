<?php

use App\Http\Controllers\Frontend\ItemController;
use App\Http\Controllers\Frontend\TransactionController;
use App\Http\Controllers\Frontend\RatingController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\MessageController;
use App\Http\Controllers\Frontend\SearchController;

use App\Http\Controllers\UniAdmin\UniAdminDashboardController;
use App\Http\Controllers\UniAdmin\UniAdminUserController;
use App\Http\Controllers\UniAdmin\UniAdminItemController;
use App\Http\Controllers\UniAdmin\UniAdminTransactionController;
use App\Http\Controllers\UniAdmin\UniAdminPenaltyController;
use App\Http\Controllers\UniAdmin\UniAdminReportController;



use App\Http\Controllers\Auth\SuperAdminSessionController;
use App\Http\Controllers\Auth\UniAdminSessionController;

use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminUniversityController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\SuperAdminReportController;

use App\Http\Controllers\Auth\UniversityApplicationController;


use Illuminate\Support\Facades\Route;


// ============================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================

/**
 * Landing page — shows welcome view for guests,
 * redirects logged-in users to their role dashboard.
 */
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        return match($role) {
            'super_admin' => redirect()->route('super-admin.dashboard'),
            'uni_admin'   => redirect()->route('uni-admin.dashboard'),
            default       => redirect()->route('home'),
        };
    }
    return view('welcome');
})->name('landing');

/**
 * Student home — the main items/marketplace feed (verified users only)
 * This is where students land after login.
 */
Route::get('/home', [ItemController::class, 'home'])->middleware(['auth', 'verified_user'])->name('home');

/**
 * Public item browsing (no login required)
 */
Route::get('/items', [ItemController::class, 'index'])->name('frontend.items.index');
Route::get('/items/{item}', [ItemController::class, 'show'])->name('frontend.items.show');

/**
 * Public search
 */
Route::get('/search', [SearchController::class, 'index'])->name('frontend.search.index');
Route::get('/search/advanced', [SearchController::class, 'advanced'])->name('frontend.search.advanced');
Route::get('/search/popular', [SearchController::class, 'popular'])->name('frontend.search.popular');
Route::get('/search/new', [SearchController::class, 'new'])->name('frontend.search.new');
Route::get('/search/category', [SearchController::class, 'byCategory'])->name('frontend.search.category');
Route::get('/search/owner-rating', [SearchController::class, 'byOwnerRating'])->name('frontend.search.owner-rating');
Route::get('/search/user/{user}', [SearchController::class, 'byOwner'])->name('frontend.search.by-owner');

/**
 * Public user profiles
 */
Route::get('/user/{user}', [ProfileController::class, 'show'])->name('frontend.profile.show');
Route::get('/user/{user}/items', [ProfileController::class, 'items'])->name('frontend.profile.items');

/**
 * University application form (anyone can apply to register their university)
 */
Route::get('/university/apply', [UniversityApplicationController::class, 'create'])->name('university.apply');
Route::post('/university/apply', [UniversityApplicationController::class, 'store'])->name('university.apply.store');
Route::get('/university/apply/submitted', [UniversityApplicationController::class, 'submitted'])->name('university.apply.submitted');

/**
 * Registration pending page — shown after student registers, before uni admin verifies
 */
Route::get('/register/pending', fn() => view('auth.pending'))->name('register.pending');


// ============================================================
// PUBLIC API ENDPOINTS
// ============================================================

Route::prefix('api')->name('api.public.')->group(function () {
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/search/filters', [SearchController::class, 'getFilters'])->name('search.filters');

    // Used by register page JS to filter universities by state
    Route::get('/universities/by-state', [UniversityApplicationController::class, 'byState'])->name('universities.by-state');
});


// ============================================================
// STUDENT / TEACHER ROUTES (auth + verified_user)
// ============================================================

Route::middleware(['auth', 'verified_user'])
    ->prefix('frontend')
    ->name('frontend.')
    ->group(function () {

        // ----------------------------------------
        // PROFILE
        // ----------------------------------------

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::get('/profile/preferences', [ProfileController::class, 'preferences'])->name('profile.preferences');
        Route::patch('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences.update');
        Route::get('/profile/active-items', [ProfileController::class, 'activeItems'])->name('profile.active-items');
        Route::delete('/profile', [ProfileController::class, 'confirmDelete'])->name('profile.delete');
        Route::get('/profile/export-data', [ProfileController::class, 'exportData'])->name('profile.export');


        // ----------------------------------------
        // ITEMS
        // ----------------------------------------

        Route::resource('items', ItemController::class)->except(['index', 'show']);
        Route::get('/my-items', [ItemController::class, 'myItems'])->name('items.my');

        // Item transaction actions
        Route::post('/items/{item}/request', [ItemController::class, 'requestTransaction'])->name('items.request');
        Route::post('/items/{item}/cancel-reservation', [ItemController::class, 'cancelReservation'])->name('items.cancel');
        Route::post('/items/{item}/mark-borrowed/{transaction}', [ItemController::class, 'markAsBorrowed'])->name('items.borrowed');
        Route::post('/items/{item}/mark-returned/{transaction}', [ItemController::class, 'markAsReturned'])->name('items.returned');
        Route::post('/items/{item}/mark-sold/{transaction}', [ItemController::class, 'markAsSold'])->name('items.sold');


        // ----------------------------------------
        // TRANSACTIONS
        // ----------------------------------------

        Route::resource('transactions', TransactionController::class)->only(['index', 'show', 'update']);
        Route::get('/transactions/{transaction}/penalties', [TransactionController::class, 'penalties'])->name('transactions.penalties');
        Route::post('/penalties/{penalty}/pay', [TransactionController::class, 'payPenalty'])->name('penalties.pay');
        Route::post('/penalties/{penalty}/waiver', [TransactionController::class, 'requestWaiver'])->name('penalties.waiver');
        Route::get('/borrowing-history', [TransactionController::class, 'borrowingHistory'])->name('transactions.borrowing-history');
        Route::get('/lending-history', [TransactionController::class, 'lendingHistory'])->name('transactions.lending-history');


        // ----------------------------------------
        // RATINGS
        // ----------------------------------------

        Route::resource('ratings', RatingController::class)->only(['show', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('/user-ratings/{user}', [RatingController::class, 'index'])->name('ratings.user');
        Route::get('/given-ratings/{user}', [RatingController::class, 'userGivenRatings'])->name('ratings.given');


        // ----------------------------------------
        // MESSAGES
        // ----------------------------------------

        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{conversation}/send', [MessageController::class, 'sendMessage'])->name('messages.send');
        Route::post('/messages/start/{user}', [MessageController::class, 'startConversation'])->name('messages.start');
        Route::delete('/messages/{conversation}', [MessageController::class, 'deleteConversation'])->name('messages.delete');
        Route::delete('/message/{message}', [MessageController::class, 'deleteMessage'])->name('message.delete');
        Route::patch('/messages/{conversation}/read', [MessageController::class, 'markConversationAsRead'])->name('messages.mark-read');


        // ----------------------------------------
        // SEARCH (authenticated extras)
        // ----------------------------------------

        Route::get('/search/recommended', [SearchController::class, 'recommended'])->name('search.recommended');
        Route::get('/search/saved', [SearchController::class, 'saved'])->name('search.saved');
        Route::get('/search/users', [SearchController::class, 'users'])->name('search.users');


        // ----------------------------------------
        // AJAX / API (authenticated)
        // ----------------------------------------

        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread');
            Route::get('/messages/recent', [MessageController::class, 'recentConversations'])->name('messages.recent');
            Route::get('/messages/{conversation}/messages', [MessageController::class, 'getMessages'])->name('messages.get');
            Route::get('/transaction-stats', [TransactionController::class, 'stats'])->name('transactions.stats');
            Route::get('/item-ratings', [RatingController::class, 'itemRatings'])->name('ratings.item');
            Route::get('/top-rated-users', [RatingController::class, 'topRatedUsers'])->name('ratings.top');
        });
    });


// ============================================================
// UNIVERSITY ADMIN ROUTES (auth + uni_admin role)
// ============================================================

Route::get('/uni-admin/login', [UniAdminSessionController::class, 'create'])
    ->name('uni-admin.login');

Route::post('/uni-admin/login', [UniAdminSessionController::class, 'store'])
    ->name('uni-admin.login.store');

Route::post('/uni-admin/logout', [UniAdminSessionController::class, 'destroy'])
    ->name('uni-admin.logout')
    ->middleware('auth');

Route::middleware(['auth', 'uni_admin'])
    ->prefix('uni-admin')
    ->name('uni-admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [UniAdminDashboardController::class, 'index'])->name('dashboard');

        // User verification — core responsibility of uni admin
        Route::get('/users', [UniAdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UniAdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/verify', [UniAdminUserController::class, 'verify'])->name('users.verify');
        Route::post('/users/{user}/reject', [UniAdminUserController::class, 'reject'])->name('users.reject');
        Route::post('/users/{user}/suspend', [UniAdminUserController::class, 'suspend'])->name('users.suspend');

        // Item oversight (only items within their university)
        Route::get('/items', [UniAdminItemController::class, 'index'])->name('items.index');
        Route::get('/items/{item}', [UniAdminItemController::class, 'show'])->name('items.show');
        Route::post('/items/{item}/flag', [UniAdminItemController::class, 'flag'])->name('items.flag');
        Route::delete('/items/{item}', [UniAdminItemController::class, 'destroy'])->name('items.destroy');

        // Transaction oversight (only within their university)
        Route::get('/transactions', [UniAdminTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [UniAdminTransactionController::class, 'show'])->name('transactions.show');

        // Penalty management (only within their university)
        Route::get('/penalties', [UniAdminPenaltyController::class, 'index'])->name('penalties.index');
        Route::get('/penalties/{penalty}', [UniAdminPenaltyController::class, 'show'])->name('penalties.show');
        Route::post('/penalties/{penalty}/waive', [UniAdminPenaltyController::class, 'waive'])->name('penalties.waive');
        Route::post('/penalties/{penalty}/mark-paid', [UniAdminPenaltyController::class, 'markPaid'])->name('penalties.mark-paid');

        // Reports (scoped to their university only)
        Route::get('/reports', [UniAdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/users', [UniAdminReportController::class, 'userReport'])->name('reports.users');
        Route::get('/reports/transactions', [UniAdminReportController::class, 'transactionReport'])->name('reports.transactions');
        Route::get('/reports/penalties', [UniAdminReportController::class, 'penaltyReport'])->name('reports.penalties');
        Route::post('/reports/export', [UniAdminReportController::class, 'export'])->name('reports.export');
    });


// ============================================================
// SUPER ADMIN ROUTES (auth + super_admin role)
// ============================================================

Route::get('/super-admin/login', [SuperAdminSessionController::class, 'create'])
    ->name('super-admin.login');

Route::post('/super-admin/login', [SuperAdminSessionController::class, 'store'])
    ->name('super-admin.login.store');

// Logout — only accessible if authenticated (no middleware needed;
// the controller just calls Auth::logout() regardless)
Route::post('/super-admin/logout', [SuperAdminSessionController::class, 'destroy'])
    ->name('super-admin.logout')
    ->middleware('auth');


Route::middleware(['auth', 'super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        // University management — core responsibility of super admin
        Route::get('/universities', [SuperAdminUniversityController::class, 'index'])->name('universities.index');
        Route::get('/universities/{university}', [SuperAdminUniversityController::class, 'show'])->name('universities.show');
        Route::post('/universities/{university}/approve', [SuperAdminUniversityController::class, 'approve'])->name('universities.approve');
        Route::post('/universities/{university}/reject', [SuperAdminUniversityController::class, 'reject'])->name('universities.reject');
        Route::post('/universities/{university}/suspend', [SuperAdminUniversityController::class, 'suspend'])->name('universities.suspend');
        Route::delete('/universities/{university}', [SuperAdminUniversityController::class, 'destroy'])->name('universities.destroy');
       
        // Uni admin credential management

        Route::post('/universities/{university}/update-credentials', [SuperAdminUniversityController::class, 'updateCredentials'])->name('universities.update-credentials');
        // After approving a university, super admin issues login credentials to the uni admin
        Route::post('/universities/{university}/issue-credentials', [SuperAdminUniversityController::class, 'issueCredentials'])->name('universities.issue-credentials');
        Route::post('/universities/{university}/reset-credentials', [SuperAdminUniversityController::class, 'resetCredentials'])->name('universities.reset-credentials');

        // Global user oversight (all universities)
        Route::get('/users', [SuperAdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [SuperAdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/suspend', [SuperAdminUserController::class, 'suspend'])->name('users.suspend');
        Route::delete('/users/{user}', [SuperAdminUserController::class, 'destroy'])->name('users.destroy');

        // Platform-wide reports
        Route::get('/reports', [SuperAdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/universities', [SuperAdminReportController::class, 'universityReport'])->name('reports.universities');
        Route::get('/reports/platform-overview', [SuperAdminReportController::class, 'platformOverview'])->name('reports.platform-overview');
        Route::get('/reports/user-growth', [SuperAdminReportController::class, 'userGrowth'])->name('reports.user-growth');
        Route::post('/reports/export', [SuperAdminReportController::class, 'export'])->name('reports.export');

        // AJAX
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/quick-stats', [SuperAdminDashboardController::class, 'quickStats'])->name('quick-stats');
            Route::get('/universities/pending-count', [SuperAdminUniversityController::class, 'pendingCount'])->name('universities.pending-count');
        });
    });


// ============================================================
// LARAVEL BREEZE AUTH ROUTES
// ============================================================

require __DIR__.'/auth.php';