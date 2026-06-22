<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrgStructureController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('lang/{locale}', function ($locale) {
    if (! in_array($locale, ['en', 'ja', 'zh-CN'])) {
        abort(400);
    }
    session(['locale' => $locale]);

    return redirect()->back();
})->name('lang.switch');

Route::get('error-logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware('auth');

Route::group(['middleware' => ['auth', 'optimizeImages']], function () {
    // DEXIE SYNC DEMO
    if (config('laravelpwa.offline_sync_enabled')) {
        Route::get('dexie-demo', fn () => view('pages.dexie-demo'))->name('dexie-demo');
    }

    // PROFILE
    Route::get('profile/{id}', [UserController::class, 'profile'])->name('profile');

    // NOTIFICATION
    Route::get('test-notification', [NotificationController::class, 'testNotification'])->name('test-notification');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');

    // AI
    Route::get('ai-testing', [AiController::class, 'index'])->name('ai-testing')->middleware('permission:ai access');

    Route::group(['middleware' => 'permission:user impersonate'], function () {
        Route::get('/impersonate/start/{id}', [ImpersonateController::class, 'start'])->name('impersonate.start');
        Route::get('/impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');
    });

    // ORG STRUCTURES ROUTES
    Route::group(['middleware' => 'permission:org structure access'], function () {
        Route::get('org-structures', [OrgStructureController::class, 'index'])->name('org-structure.index');
        Route::get('org-structure', [OrgStructureController::class, 'create'])->name('org-structure.create')->middleware('permission:org structure create');
        Route::post('org-structure', [OrgStructureController::class, 'store'])->name('org-structure.store')->middleware('permission:org structure create');

        Route::get('org-structure/{id}', [OrgStructureController::class, 'show'])->name('org-structure.show');

        Route::get('org-structure/{id}/edit', [OrgStructureController::class, 'edit'])->name('org-structure.edit')->middleware('permission:org structure edit');
        Route::post('org-structure/{id}', [OrgStructureController::class, 'update'])->name('org-structure.update')->middleware('permission:org structure edit');
    });

    // POSITIONS ROUTES
    Route::group(['middleware' => 'permission:position access'], function () {
        Route::get('positions', [PositionController::class, 'index'])->name('position.index');
        Route::get('position/create', [PositionController::class, 'create'])->name('position.create')->middleware('permission:position create');
        Route::post('position', [PositionController::class, 'store'])->name('position.store')->middleware('permission:position create');

        Route::get('position/{id}', [PositionController::class, 'show'])->name('position.show');

        Route::get('position/{id}/edit', [PositionController::class, 'edit'])->name('position.edit')->middleware('permission:position edit');
        Route::post('position/{id}', [PositionController::class, 'update'])->name('position.update')->middleware('permission:position edit');
    });

    // COMPANIES ROUTES
    Route::group(['middleware' => 'permission:company access'], function () {
        Route::get('companies', [CompanyController::class, 'index'])->name('company.index');
        Route::get('company/create', [CompanyController::class, 'create'])->name('company.create')->middleware('permission:company create');
        Route::post('company', [CompanyController::class, 'store'])->name('company.store')->middleware('permission:company create');

        Route::get('company/{id}', [CompanyController::class, 'show'])->name('company.show');

        Route::get('company/{id}/edit', [CompanyController::class, 'edit'])->name('company.edit')->middleware('permission:company edit');
        Route::post('company/{id}', [CompanyController::class, 'update'])->name('company.update')->middleware('permission:company edit');
    });

    // ROLES ROUTES
    Route::group(['middleware' => 'permission:role access'], function () {
        Route::get('roles', [RoleController::class, 'index'])->name('role.index');
        Route::get('role/create', [RoleController::class, 'create'])->name('role.create')->middleware('permission:role create');
        Route::post('role', [RoleController::class, 'store'])->name('role.store')->middleware('permission:role create');

        Route::get('role/{id}', [RoleController::class, 'show'])->name('role.show');

        Route::get('role/{id}/edit', [RoleController::class, 'edit'])->name('role.edit')->middleware('permission:role edit');
        Route::post('role/{id}', [RoleController::class, 'update'])->name('role.update')->middleware('permission:role edit');
    });

    // USERS ROUTES
    Route::group(['middleware' => 'permission:user access'], function () {
        Route::get('/user/trash', [UserController::class, 'trash'])->name('user.trash');
        Route::post('/user/{id}/restore', [UserController::class, 'restore'])->name('user.restore');
        Route::delete('/user/{id}/force-delete', [UserController::class, 'forceDelete'])->name('user.force_delete');

        Route::get('users', [UserController::class, 'index'])->name('user.index');
        Route::get('user/create', [UserController::class, 'create'])->name('user.create')->middleware('permission:user create');
        Route::post('user', [UserController::class, 'store'])->name('user.store')->middleware('permission:user create');

        Route::get('user/{id}', [UserController::class, 'show'])->name('user.show');

        Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware('permission:user edit');
        Route::post('user/{id}', [UserController::class, 'update'])->name('user.update')->middleware('permission:user edit');
    });

    // SYSTEM SETTING
    Route::group(['middleware' => 'permission:system settings'], function () {
        Route::get('system-setting', [SystemSettingController::class, 'index'])->name('system-setting.index');
    });

    // SYSTEM LOG ROUTES
    Route::group(['middleware' => 'permission:system logs'], function () {
        Route::get('system-logs', [SystemLogController::class, 'index'])->name('system-logs');
    });

    // SYSTEM TRASH BIN
    Route::group(['middleware' => 'permission:trash bin'], function () {
        Route::get('trash-bin', function () {
            return view('pages.trash.index');
        })->name('trash.index');
    });

});

Route::get('/home', [HomeController::class, 'index'])->name('home');

// TICKETS — all authenticated users with ticket access or ticket responder
Route::middleware(['auth', 'permission:ticket access|ticket responder'])->group(function () {
    Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('ticket/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('ticket', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('ticket/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('ticket/{id}/comment', [TicketController::class, 'storeComment'])->name('tickets.comment.store');
    Route::delete('ticket/{id}/comment/{commentId}', [TicketController::class, 'destroyComment'])->name('tickets.comment.destroy');
    Route::patch('ticket/{id}/status', [TicketController::class, 'updateStatus'])->name('tickets.status.update');
    Route::post('ticket/{id}/attachment', [TicketController::class, 'storeAttachment'])->name('tickets.attachment.store');
    Route::delete('ticket/{id}/attachment/{attachmentId}', [TicketController::class, 'destroyAttachment'])->name('tickets.attachment.destroy');
});

// TICKETS — responders only
Route::middleware(['auth', 'permission:ticket responder'])->group(function () {
    Route::get('ticket/{id}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::put('ticket/{id}', [TicketController::class, 'update'])->name('tickets.update');
    Route::patch('ticket/{id}/assignee', [TicketController::class, 'updateAssignee'])->name('tickets.assignee.update');
});
