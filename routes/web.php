<?php

use App\Http\Controllers\Frontend\ItemController;
use App\Http\Controllers\Frontend\TransactionController;
use App\Http\Controllers\Frontend\RatingController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\MessageController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserManagementController;
use App\Http\Controllers\Admin\AdminItemManagementController;
use App\Http\Controllers\Admin\AdminTransactionManagementController;
use App\Http\Controllers\Admin\AdminPenaltyManagementController;
use App\Http\Controllers\Admin\AdminReportController;
use Illuminate\Support\Facades\Route;


// ========================================
// PUBLIC ROUTES (No Authentication Required)
// ========================================

/**
 * Home page
 */
Route::get('/', function () {
    return view('welcome');
})->name('home');

/**
 * Browse items (public search)
 */
Route::get('/search', [SearchController::class, 'index'])->name('frontend.search.index');
Route::get('/search/advanced', [SearchController::class, 'advanced'])->name('frontend.search.advanced');
Route::get('/search/users', [SearchController::class, 'users'])->name('frontend.search.users');
Route::get('/search/popular', [SearchController::class, 'popular'])->name('frontend.search.popular');
Route::get('/search/new', [SearchController::class, 'new'])->name('frontend.search.new');
Route::get('/search/category', [SearchController::class, 'byCategory'])->name('frontend.search.category');
Route::get('/search/owner-rating', [SearchController::class, 'byOwnerRating'])->name('frontend.search.owner-rating');
Route::get('/search/user/{user}', [SearchController::class, 'byOwner'])->name('frontend.search.by-owner');

/**
 * Public item listing and view
 */
Route::get('/items', [ItemController::class, 'index'])->name('frontend.items.index');
Route::get('/items/{item}', [ItemController::class, 'show'])->name('frontend.items.show');

/**
 * Public user profiles
 */
Route::get('/user/{user}', [ProfileController::class, 'show'])->name('frontend.profile.show');
Route::get('/user/{user}/items', [ProfileController::class, 'items'])->name('frontend.profile.items');
Route::get('/user/{user}/history', [ProfileController::class, 'transactionHistory'])->name('frontend.profile.history');
Route::get('/user/{user}/badges', [ProfileController::class, 'badges'])->name('frontend.profile.badges');
Route::get('/user/{user}/statistics', [ProfileController::class, 'statistics'])->name('frontend.profile.statistics');


// ========================================
// API ROUTES (Public JSON Endpoints)
// ========================================

Route::prefix('api')->name('frontend.api.')->group(function () {
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/search/filters', [SearchController::class, 'getFilters'])->name('search.filters');
    Route::get('/search/statistics', [SearchController::class, 'statistics'])->name('search.statistics');
    Route::get('/user/{user}', [ProfileController::class, 'publicApi'])->name('user');
});


// ========================================
// AUTHENTICATED ROUTES (Login Required)
// ========================================

Route::middleware(['auth', 'verified'])->prefix('frontend')->name('frontend.')->group(function () {

    // ========================================
    // DASHBOARD & PROFILE
    // ========================================

    /**
     * Main dashboard
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/borrower', [DashboardController::class, 'borrowerDashboard'])->name('dashboard.borrower');
    Route::get('/dashboard/lender', [DashboardController::class, 'lenderDashboard'])->name('dashboard.lender');
    Route::get('/dashboard/profile', [DashboardController::class, 'profileDashboard'])->name('dashboard.profile');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analyticsDashboard'])->name('dashboard.analytics');
    Route::get('/dashboard/notifications', [DashboardController::class, 'notifications'])->name('dashboard.notifications');

    /**
     * User profile management
     */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/profile/preferences', [ProfileController::class, 'preferences'])->name('profile.preferences');
    Route::patch('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences.update');
    Route::get('/profile/active-items', [ProfileController::class, 'activeItems'])->name('profile.active-items');
    Route::get('/profile/delete', [ProfileController::class, 'deleteAccount'])->name('profile.delete');
    Route::post('/profile/delete', [ProfileController::class, 'confirmDelete'])->name('profile.delete.confirm');
    Route::get('/profile/export-data', [ProfileController::class, 'exportData'])->name('profile.export');


    // ========================================
    // ITEMS MANAGEMENT
    // ========================================

    /**
     * Item CRUD operations
     */
    Route::resource('items', ItemController::class)->except(['index', 'show']);
    Route::get('/my-items', [ItemController::class, 'myItems'])->name('items.my');

    /**
     * Item transactions
     */
    Route::post('/items/{item}/request', [ItemController::class, 'requestTransaction'])->name('items.request');
    Route::post('/items/{item}/cancel-reservation', [ItemController::class, 'cancelReservation'])->name('items.cancel');
    Route::post('/items/{item}/mark-borrowed/{transaction}', [ItemController::class, 'markAsBorrowed'])->name('items.borrowed');
    Route::post('/items/{item}/mark-returned/{transaction}', [ItemController::class, 'markAsReturned'])->name('items.returned');
    Route::post('/items/{item}/mark-sold/{transaction}', [ItemController::class, 'markAsSold'])->name('items.sold');


    // ========================================
    // TRANSACTIONS
    // ========================================

    /**
     * Transaction management
     */
    Route::resource('transactions', TransactionController::class)->only(['index', 'show', 'update']);
    Route::get('/transactions/{transaction}/penalties', [TransactionController::class, 'penalties'])->name('transactions.penalties');
    Route::post('/penalties/{penalty}/pay', [TransactionController::class, 'payPenalty'])->name('penalties.pay');
    Route::post('/penalties/{penalty}/waiver', [TransactionController::class, 'requestWaiver'])->name('penalties.waiver');
    Route::get('/borrowing-history', [TransactionController::class, 'borrowingHistory'])->name('transactions.borrowing-history');
    Route::get('/lending-history', [TransactionController::class, 'lendingHistory'])->name('transactions.lending-history');


    // ========================================
    // RATINGS
    // ========================================

    /**
     * Rating management
     */
    Route::resource('ratings', RatingController::class)->only(['show', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/user-ratings/{user}', [RatingController::class, 'index'])->name('ratings.user');
    Route::get('/given-ratings/{user}', [RatingController::class, 'userGivenRatings'])->name('ratings.given');
    Route::post('/export-ratings/{user}', [RatingController::class, 'exportRatings'])->name('ratings.export');


    // ========================================
    // MESSAGES
    // ========================================

    /**
     * Messaging system
     */
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}/send', [MessageController::class, 'sendMessage'])->name('messages.send');
    Route::post('/messages/start/{user}', [MessageController::class, 'startConversation'])->name('messages.start');
    Route::delete('/messages/{conversation}/delete', [MessageController::class, 'deleteConversation'])->name('messages.delete');
    Route::delete('/message/{message}/delete', [MessageController::class, 'deleteMessage'])->name('message.delete');
    Route::patch('/messages/{conversation}/read', [MessageController::class, 'markConversationAsRead'])->name('messages.mark-read');
    Route::patch('/message/{message}/read', [MessageController::class, 'markAsRead'])->name('message.mark-read');
    Route::get('/messages/search', [MessageController::class, 'searchConversations'])->name('messages.search');


    // ========================================
    // SEARCH (Authenticated)
    // ========================================

    /**
     * Authenticated search features
     */
    Route::get('/search/recommended', [SearchController::class, 'recommended'])->name('search.recommended');
    Route::get('/search/saved', [SearchController::class, 'saved'])->name('search.saved');
    Route::post('/search/export', [SearchController::class, 'exportResults'])->name('search.export');


    // ========================================
    // API ENDPOINTS (Authenticated)
    // ========================================

    Route::prefix('api')->name('api.')->group(function () {
        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class, 'quickStats'])->name('dashboard.stats');

        // Messages
        Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread');
        Route::get('/messages/recent', [MessageController::class, 'recentConversations'])->name('messages.recent');
        Route::get('/messages/{conversation}/get', [MessageController::class, 'getMessages'])->name('messages.get');
        Route::get('/messages/statistics', [MessageController::class, 'statistics'])->name('messages.statistics');

        // Ratings
        Route::get('/item-ratings', [RatingController::class, 'itemRatings'])->name('ratings.item');
        Route::get('/user-ratings/{user}', [RatingController::class, 'userProfileRatings'])->name('ratings.user');
        Route::get('/top-rated-users', [RatingController::class, 'topRatedUsers'])->name('ratings.top');
        Route::get('/rating-statistics', [RatingController::class, 'statistics'])->name('ratings.statistics');

        // Transactions
        Route::get('/transaction-stats', [TransactionController::class, 'stats'])->name('transactions.stats');
    });
});


// ========================================
// ADMIN ROUTES (Authentication + Admin Role Required)
// ========================================

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ========================================
        // ADMIN DASHBOARD
        // ========================================

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/quick-stats', [AdminDashboardController::class, 'quickStats'])->name('api.quick-stats');
        Route::post('/export-report', [AdminDashboardController::class, 'exportReport'])->name('export-report');


        // ========================================
        // USER MANAGEMENT
        // ========================================

        Route::resource('users', AdminUserManagementController::class);
        Route::post('users/{user}/block', [AdminUserManagementController::class, 'toggleBlock'])->name('users.block');
        Route::post('users/{user}/promote-admin', [AdminUserManagementController::class, 'promoteAdmin'])->name('users.promote');
        Route::post('users/{user}/demote-admin', [AdminUserManagementController::class, 'demoteAdmin'])->name('users.demote');
        Route::post('users/{user}/verify-email', [AdminUserManagementController::class, 'verifyEmail'])->name('users.verify-email');
        Route::post('users/{user}/resend-verification', [AdminUserManagementController::class, 'resendVerification'])->name('users.resend-verification');
        Route::post('users/{user}/reset-password', [AdminUserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/warning', [AdminUserManagementController::class, 'issueWarning'])->name('users.warning');
        Route::get('users/{user}/transactions', [AdminUserManagementController::class, 'transactions'])->name('users.transactions');
        Route::get('users/{user}/penalties', [AdminUserManagementController::class, 'penalties'])->name('users.penalties');
        Route::get('users/{user}/ratings', [AdminUserManagementController::class, 'ratings'])->name('users.ratings');
        Route::delete('ratings/{rating}', [AdminUserManagementController::class, 'deleteRating'])->name('ratings.delete');
        Route::post('penalties/{penalty}/waive', [AdminUserManagementController::class, 'waivePenalty'])->name('penalties.waive');
        Route::post('users/export', [AdminUserManagementController::class, 'exportUsers'])->name('users.export');
        Route::get('api/users/{user}/statistics', [AdminUserManagementController::class, 'statistics'])->name('api.users.statistics');


        // ========================================
        // ITEM MANAGEMENT
        // ========================================

        Route::resource('items', AdminItemManagementController::class);
        Route::post('items/{item}/flag', [AdminItemManagementController::class, 'flag'])->name('items.flag');
        Route::post('items/{item}/unflag', [AdminItemManagementController::class, 'unflag'])->name('items.unflag');
        Route::post('items/{item}/approve', [AdminItemManagementController::class, 'approve'])->name('items.approve');
        Route::post('items/{item}/reject', [AdminItemManagementController::class, 'reject'])->name('items.reject');
        Route::get('items/{item}/transactions', [AdminItemManagementController::class, 'transactions'])->name('items.transactions');
        Route::get('items/{item}/ratings', [AdminItemManagementController::class, 'ratings'])->name('items.ratings');
        Route::get('items/{item}/similar', [AdminItemManagementController::class, 'similar'])->name('items.similar');
        Route::delete('items/{item}/rating/{rating}', [AdminItemManagementController::class, 'deleteRating'])->name('items.delete-rating');
        Route::post('items/{item}/dispute-rating/{rating}', [AdminItemManagementController::class, 'resolveDisputeRating'])->name('items.resolve-dispute');
        Route::post('items/bulk-action', [AdminItemManagementController::class, 'bulkAction'])->name('items.bulk-action');
        Route::post('items/export', [AdminItemManagementController::class, 'exportItems'])->name('items.export');
        Route::get('api/items/{item}/statistics', [AdminItemManagementController::class, 'statistics'])->name('api.items.statistics');


        // ========================================
        // TRANSACTION MANAGEMENT
        // ========================================

        Route::resource('transactions', AdminTransactionManagementController::class)->only(['index', 'show', 'update']);
        Route::get('transactions/{transaction}/penalties', [AdminTransactionManagementController::class, 'penalties'])->name('transactions.penalties');
        Route::post('transactions/{transaction}/penalty', [AdminTransactionManagementController::class, 'createPenalty'])->name('transactions.create-penalty');
        Route::post('penalties/{penalty}/approve', [AdminTransactionManagementController::class, 'approvePenalty'])->name('penalties.approve');
        Route::post('penalties/{penalty}/waive', [AdminTransactionManagementController::class, 'waivePenalty'])->name('transaction-penalties.waive');
        Route::get('transactions/{transaction}/ratings', [AdminTransactionManagementController::class, 'ratings'])->name('transactions.ratings');
        Route::post('ratings/{rating}/dispute', [AdminTransactionManagementController::class, 'resolveRatingDispute'])->name('ratings.dispute');
        Route::post('transactions/{transaction}/mediation', [AdminTransactionManagementController::class, 'requestMediation'])->name('transactions.mediation');
        Route::post('transactions/{transaction}/resolve-dispute', [AdminTransactionManagementController::class, 'resolveDispute'])->name('transactions.resolve-dispute');
        Route::post('transactions/export', [AdminTransactionManagementController::class, 'exportTransactions'])->name('transactions.export');
        Route::get('api/transactions/statistics', [AdminTransactionManagementController::class, 'statistics'])->name('api.transactions.statistics');


        // ========================================
        // PENALTY MANAGEMENT
        // ========================================

        Route::resource('penalties', AdminPenaltyManagementController::class)->only(['index', 'show']);
        Route::post('penalties/{penalty}/mark-paid', [AdminPenaltyManagementController::class, 'markPaid'])->name('penalties.mark-paid');
        Route::get('penalties/{penalty}/waive', [AdminPenaltyManagementController::class, 'showWaiveForm'])->name('penalties.waive-form');
        Route::post('penalties/{penalty}/waive', [AdminPenaltyManagementController::class, 'waive'])->name('penalties.waive');
        Route::post('penalties/{penalty}/request-payment', [AdminPenaltyManagementController::class, 'requestPayment'])->name('penalties.request-payment');
        Route::post('penalties/{penalty}/send-reminder', [AdminPenaltyManagementController::class, 'sendReminder'])->name('penalties.send-reminder');
        Route::post('penalties/bulk-approve', [AdminPenaltyManagementController::class, 'bulkApprove'])->name('penalties.bulk-approve');
        Route::post('penalties/bulk-waive', [AdminPenaltyManagementController::class, 'bulkWaive'])->name('penalties.bulk-waive');
        Route::get('penalties/report', [AdminPenaltyManagementController::class, 'report'])->name('penalties.report');
        Route::post('penalties/export', [AdminPenaltyManagementController::class, 'exportPenalties'])->name('penalties.export');
        Route::get('api/penalties/statistics', [AdminPenaltyManagementController::class, 'statistics'])->name('api.penalties.statistics');


        // ========================================
        // REPORTS
        // ========================================

        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/platform-overview', [AdminReportController::class, 'platformOverview'])->name('reports.platform-overview');
        Route::get('/reports/user-analytics', [AdminReportController::class, 'userAnalytics'])->name('reports.user-analytics');
        Route::get('/reports/transaction-analytics', [AdminReportController::class, 'transactionAnalytics'])->name('reports.transaction-analytics');
        Route::get('/reports/item-analytics', [AdminReportController::class, 'itemAnalytics'])->name('reports.item-analytics');
        Route::get('/reports/rating-analytics', [AdminReportController::class, 'ratingAnalytics'])->name('reports.rating-analytics');
        Route::get('/reports/penalty-analytics', [AdminReportController::class, 'penaltyAnalytics'])->name('reports.penalty-analytics');
        Route::get('/reports/revenue', [AdminReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/user-growth', [AdminReportController::class, 'userGrowth'])->name('reports.user-growth');
        Route::get('/reports/system-health', [AdminReportController::class, 'systemHealth'])->name('reports.system-health');
        Route::post('/reports/generate', [AdminReportController::class, 'generate'])->name('reports.generate');
        Route::post('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/schedule', [AdminReportController::class, 'schedule'])->name('reports.schedule');
        Route::post('/reports/schedule', [AdminReportController::class, 'storeSchedule'])->name('reports.store-schedule');
    });


// ========================================
// LARAVEL BREEZE AUTH ROUTES
// ========================================

require __DIR__.'/auth.php';
