<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicMonitorController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root → redirect sesuai status login
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Public Monitor (tanpa login)
|--------------------------------------------------------------------------
*/
Route::get('/monitor', [PublicMonitorController::class, 'index'])->name('monitor');
Route::get('/monitor/data', [PublicMonitorController::class, 'data'])->name('monitor.data');

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Auth Area
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard/monthly-stats', [DashboardController::class, 'monthlyStats'])
        ->name('dashboard.monthly-stats');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])
        ->name('wo.show');
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/work-orders', [WorkOrderController::class, 'adminIndex'])->name('wo.index');
        Route::get('/work-orders/create', [WorkOrderController::class, 'create'])->name('wo.create');
        Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('wo.store');
        Route::get('/work-orders/data', [WorkOrderController::class, 'adminData'])->name('wo.data');
        Route::delete('/work-orders/{workOrder}', [WorkOrderController::class, 'destroy'])
            ->name('wo.destroy');
        Route::get('/work-orders/cctv', fn() => redirect()
            ->route('admin.wo.index', ['division' => 'cctv']))
            ->name('wo.cctv');
        Route::get('/work-orders/template', [WorkOrderController::class, 'downloadTemplate'])
            ->name('wo.template');
        Route::get('/work-orders/import', [WorkOrderController::class, 'importForm'])
            ->name('wo.import-form');
        Route::post('/work-orders/import', [WorkOrderController::class, 'import'])
            ->name('wo.import');
        Route::get('/work-orders/helpdesk', fn() => redirect()
            ->route('admin.wo.index', ['division' => 'helpdesk']))
            ->name('wo.helpdesk');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active');
        Route::delete('/users/{user}/remove-avatar', [UserController::class, 'removeAvatar'])
            ->name('users.remove-avatar');
    });
    Route::middleware('role:admin')->prefix('report')->name('report.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/data', [ReportController::class, 'data'])->name('data');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });
    Route::middleware('role:admin')->group(function () {
        Route::get('/team-settings', [TeamSettingController::class, 'index'])
            ->name('team-settings.index');
        Route::put('/team-settings/{division}', [TeamSettingController::class, 'update'])
            ->name('team-settings.update');
        Route::post('/team-settings/{division}/shuffle', [TeamSettingController::class, 'shuffleNow'])
            ->name('team-settings.shuffle');
        Route::post('/team-settings/teams', [TeamSettingController::class, 'storeTeam'])
            ->name('team-settings.teams.store');
        Route::put('/team-settings/teams/{team}', [TeamSettingController::class, 'updateTeam'])
            ->name('team-settings.teams.update');
        Route::delete('/team-settings/teams/{team}', [TeamSettingController::class, 'destroyTeam'])
            ->name('team-settings.teams.destroy');
        Route::post('/team-settings/auto-generate', [TeamSettingController::class, 'autoGenerateTeams'])
            ->name('team-settings.auto-generate');
    });
    Route::middleware('role:technician')->prefix('technician')->name('technician.')->group(function () {

        Route::get('/work-orders', [WorkOrderController::class, 'technicianIndex'])->name('wo.index');
        Route::get('/work-orders/create', [WorkOrderController::class, 'createMandiri'])->name('wo.create');
        Route::post('/work-orders', [WorkOrderController::class, 'storeMandiri'])->name('wo.store');
        Route::get('/work-orders/data', [WorkOrderController::class, 'technicianData'])->name('wo.data');
        Route::get('/work-orders/available', [WorkOrderController::class, 'availableData'])
            ->name('wo.available');
        Route::get('/report', [ReportController::class, 'technicianIndex'])
            ->name('report.index');
        Route::get('/report/data', [ReportController::class, 'technicianData'])
            ->name('report.data');
        Route::get('/report/export', [ReportController::class, 'technicianExport'])
            ->name('report.export');
        Route::middleware('role:technician,cctv')->group(function () {
            Route::get('/cctv', fn() => redirect()->route('technician.wo.index'))
                ->name('cctv.index');
        });
        Route::middleware('role:technician,helpdesk')->group(function () {
            Route::get('/helpdesk', fn() => redirect()->route('technician.wo.index'))
                ->name('helpdesk.index');
        });
    });
    Route::post('/work-orders/{workOrder}/claim', [WorkOrderController::class, 'claim'])
        ->name('wo.claim');

    Route::post('/work-orders/{workOrder}/start', [WorkOrderController::class, 'start'])
        ->name('wo.start');

    Route::post('/work-orders/{workOrder}/finish', [WorkOrderController::class, 'finish'])
        ->name('wo.finish');

    // ============ MEDIA WORK ORDER (edit/tambah/hapus) ============
    Route::post('/work-orders/{workOrder}/media', [WorkOrderController::class, 'addMedia'])
        ->name('wo.media.add');
    Route::patch('/work-orders/{workOrder}/media/{media}', [WorkOrderController::class, 'updateMedia'])
        ->name('wo.media.update');
    Route::delete('/work-orders/{workOrder}/media/{media}', [WorkOrderController::class, 'deleteMedia'])
        ->name('wo.media.delete');
});
