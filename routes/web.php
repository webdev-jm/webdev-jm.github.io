<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

use App\Http\Controllers\{
    RoleController, UserController, CompanyController,
    SystemLogController, SystemSettingController, HomeController,
    NotificationController, PositionController, OrgStructureController
};

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

Auth::routes();

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('lang/{locale}', function ($locale) {
    if (!in_array($locale, ['en', 'ja', 'zh-CN'])) {
        abort(400);
    }
    session(['locale' => $locale]);
    return redirect()->back();
})->name('lang.switch');

Route::get('error-logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware('auth');

Route::group(['middleware' => ['auth', 'optimizeImages']], function() {
    // PROFILE
    Route::get('profile/{id}', [UserController::class, 'profile'])->name('profile');

    // NOTIFICATION
    Route::get('test-notification', [NotificationController::class, 'testNotification'])->name('test-notification');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');

    // ORG STRUCTURES ROUTES
    Route::group(['middleware' => 'permission:org structure access'], function() {
        Route::get('org-structures', [OrgStructureController::class, 'index'])->name('org-structure.index');
        Route::get('org-structure', [OrgStructureController::class, 'create'])->name('org-structure.create')->middleware('permission:org structure create');
        Route::post('org-structure', [OrgStructureController::class, 'store'])->name('org-structure.store')->middleware('permission:org structure create');

        Route::get('org-structure/{id}', [OrgStructureController::class, 'show'])->name('org-structure.show');

        Route::get('org-structure/{id}/edit', [OrgStructureController::class, 'edit'])->name('org-structure.edit')->middleware('permission:org structure edit');
        Route::post('org-structure/{id}', [OrgStructureController::class, 'update'])->name('org-structure.update')->middleware('permission:org structure edit');
    });
    

    // POSITIONS ROUTES
    Route::group(['middleware' => 'permission:position access'], function() {
        Route::get('positions', [PositionController::class, 'index'])->name('position.index');
        Route::get('position/create', [PositionController::class, 'create'])->name('position.create')->middleware('permission:position create');
        Route::post('position', [PositionController::class, 'store'])->name('position.store')->middleware('permission:position create');

        Route::get('position/{id}', [PositionController::class, 'show'])->name('position.show');

        Route::get('position/{id}/edit', [PositionController::class, 'edit'])->name('position.edit')->middleware('permission:position edit');
        Route::post('position/{id}', [PositionController::class, 'update'])->name('position.update')->middleware('permission:position edit');
    });

    // COMPANIES ROUTES
    Route::group(['middleware' => 'permission:company access'], function() {
        Route::get('companies', [CompanyController::class, 'index'])->name('company.index');
        Route::get('company/create', [CompanyController::class, 'create'])->name('company.create')->middleware('permission:company create');
        Route::post('company', [CompanyController::class, 'store'])->name('company.store')->middleware('permission:company create');

        Route::get('company/{id}', [CompanyController::class, 'show'])->name('company.show');

        Route::get('company/{id}/edit', [CompanyController::class, 'edit'])->name('company.edit')->middleware('permission:company edit');
        Route::post('company/{id}', [CompanyController::class, 'update'])->name('company.update')->middleware('permission:company edit');
    });

    // ROLES ROUTES
    Route::group(['middleware' => 'permission:role access'], function() {
        Route::get('roles', [RoleController::class, 'index'])->name('role.index');
        Route::get('role/create', [RoleController::class, 'create'])->name('role.create')->middleware('permission:role create');
        Route::post('role', [RoleController::class, 'store'])->name('role.store')->middleware('permission:role create');

        Route::get('role/{id}', [RoleController::class, 'show'])->name('role.show');

        Route::get('role/{id}/edit', [RoleController::class, 'edit'])->name('role.edit')->middleware('permission:role edit');
        Route::post('role/{id}', [RoleController::class, 'update'])->name('role.update')->middleware('permission:role edit');
    });

    // USERS ROUTES
    Route::group(['middleware' => 'permission:user access'], function() {
        Route::get('users', [UserController::class, 'index'])->name('user.index');
        Route::get('user/create', [UserController::class, 'create'])->name('user.create')->middleware('permission:user create');
        Route::post('user', [UserController::class, 'store'])->name('user.store')->middleware('permission:user create');

        Route::get('user/{id}', [UserController::class, 'show'])->name('user.show');

        Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware('permission:user edit');
        Route::post('user/{id}', [UserController::class, 'update'])->name('user.update')->middleware('permission:user edit');
    });

    // SYSTEM SETTING
    Route::group(['middleware' => 'permission:system settings'], function() {
        Route::get('system-setting', [SystemSettingController::class, 'index'])->name('system-setting.index');
    });

    // SYSTEM LOG ROUTES
    Route::group(['middleware' => 'permission:system logs'], function() {
        Route::get('system-logs', [SystemLogController::class, 'index'])->name('system-logs');
    });

});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
