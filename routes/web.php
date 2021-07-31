<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false
]);


// Guest No Auth
Route::middleware('visitor')->group(function () {
    Route::get('/', [\App\Http\Controllers\Guest\HomeController::class, 'index'])->name('home');

    Route::prefix('guest')->name('guest.')->group(function () {

        Route::prefix('bps')->name('bps.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guest\BpsController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Guest\BpsController::class, 'table'])->name('table');
            Route::get('/export/{id}', [\App\Http\Controllers\Guest\BpsController::class, 'export'])->name('export');
            Route::get('/chart_data/{id}', [\App\Http\Controllers\Guest\BpsController::class, 'getUraianForChart']);
            Route::get('/chart_summary/{id}', [\App\Http\Controllers\Guest\BpsController::class, 'getSummaryUraianForChart']);
        });

        Route::prefix('rpjmd')->name('rpjmd.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guest\RpjmdController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Guest\RpjmdController::class, 'table'])->name('table');
            Route::get('/export/{id}', [\App\Http\Controllers\Guest\RpjmdController::class, 'export'])->name('export');
            Route::get('/chart_data/{id}', [\App\Http\Controllers\Guest\RpjmdController::class, 'getUraianForChart']);
        });

        Route::prefix('delapankeldata')->name('delapankeldata.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guest\DelapanKelDataController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Guest\DelapanKelDataController::class, 'table'])->name('table');
            Route::get('/export/{id}', [\App\Http\Controllers\Guest\DelapanKelDataController::class, 'export'])->name('export');
            Route::get('/chart_data/{id}', [\App\Http\Controllers\Guest\DelapanKelDataController::class, 'getUraianForChart']);
        });

        Route::prefix('indikator')->name('indikator.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guest\IndikatorController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Guest\IndikatorController::class, 'table'])->name('table');
            Route::get('/export/{id}', [\App\Http\Controllers\Guest\IndikatorController::class, 'export'])->name('export');
            Route::get('/chart_data/{id}', [\App\Http\Controllers\Guest\IndikatorController::class, 'getUraianForChart']);
        });

        Route::get('/skpd', [\App\Http\Controllers\Guest\SkpdController::class, 'index'])->name('skpd');
        Route::get('/skpd/{id}', [\App\Http\Controllers\Guest\SkpdController::class, 'delapankeldata'])->name('delapankeldata.skpd');
    });
});

// Export isi uraian
Route::prefix('exports')->name('exports.')->group(function () {
    Route::get('/rpjmd/{tabelRpjmd}', [\App\Http\Controllers\ExportController::class, 'exportRpjmd'])->name('rpjmd');
    Route::get('/bps/{tabelBps}', [\App\Http\Controllers\ExportController::class, 'exportBps'])->name('bps');
    Route::get('/delapankeldata/{tabel8KelData}', [\App\Http\Controllers\ExportController::class, 'export8KelData'])->name('delapankeldata');
    Route::get('/indikator/{tabelIndikator}', [\App\Http\Controllers\ExportController::class, 'exportIndikator'])->name('indikator');
});


Route::middleware(['auth'])->group(function () {

    // User Account 
    Route::get('/profile', [\App\Http\Controllers\AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\AccountController::class, 'updateProfile'])->name('update_profile');
    Route::get('/change_password', [\App\Http\Controllers\AccountController::class, 'password'])->name('change_password');
    Route::put('/change_password', [\App\Http\Controllers\AccountController::class, 'updatePassword'])->name('update_password');

    Route::middleware(['level:1'])->name('admin.')->prefix('admin')->group(function () {
        Route::get('/', App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');
        Route::resource('skpd', \App\Http\Controllers\Admin\SkpdController::class)->except(['show', 'create']);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::get('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'getUser']);
        Route::get('/visitor', [\App\Http\Controllers\Admin\VisitorController::class, 'index'])->name('visitor');

        Route::get('/print_reports', [\App\Http\Controllers\Admin\PrintReportController::class, 'index'])->name('print_reports');

        // Kategori SKPD [CRUD Kategori SKPD]
        // Route::resource('skpd_category', App\Http\Controllers\Admin\SkpdCategoryController::class)->except(['create', 'show', 'edit']);

        // Log Users 
        Route::get('/userlog', [\App\Http\Controllers\Admin\UserLogController::class, 'index'])->name('userlog.index');
        Route::delete('/userlog', [\App\Http\Controllers\Admin\UserLogController::class, 'destroyAll'])->name('userlog.destroyall');

        // Menu Data Pengunjung
        Route::delete('/visitor/{visitor}', [\App\Http\Controllers\Admin\VisitorController::class, 'destroy'])->name('visitor.destroy');
        Route::delete('/visitor', [\App\Http\Controllers\Admin\VisitorController::class, 'destroyAll'])->name('visitor.destroyall');

        // Menu Tree View 
        Route::name('treeview.')->prefix('treeview')->group(function () {
            Route::resource('delapankeldata', \App\Http\Controllers\Admin\TreeView\DelapanKelDataController::class)->except(['create', 'show']);
            Route::resource('rpjmd', \App\Http\Controllers\Admin\TreeView\RpjmdController::class)->except(['create', 'show']);
            Route::resource('indikator', \App\Http\Controllers\Admin\TreeView\IndikatorController::class)->except(['create', 'show']);
            Route::resource('bps', \App\Http\Controllers\Admin\TreeView\BpsController::class)->except(['create', 'show']);
        });

        // Menu Form Uraian
        Route::name('uraian.')->prefix('uraian')->group(function () {

            Route::prefix('rpjmd')->name('rpjmd.')->group(function () {
                Route::get('/{tabelRpjmd?}', [\App\Http\Controllers\Admin\Uraian\RpjmdController::class, 'index'])->name('index');
                Route::get('/{tabelRpjmd}/{uraianRpjmd}/edit', [\App\Http\Controllers\Admin\Uraian\RpjmdController::class, 'edit'])->name('edit');
                Route::post('/', [\App\Http\Controllers\Admin\Uraian\RpjmdController::class, 'store'])->name('store');
                Route::put('/{uraianRpjmd}', [\App\Http\Controllers\Admin\Uraian\RpjmdController::class, 'update'])->name('update');
                Route::delete('/{uraianRpjmd}', [\App\Http\Controllers\Admin\Uraian\RpjmdController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('delapankeldata')->name('delapankeldata.')->group(function () {
                Route::get('/{tabel8KelData?}', [\App\Http\Controllers\Admin\Uraian\DelapanKelDataController::class, 'index'])->name('index');
                Route::get('/{tabel8KelData}/{uraian8KelData}/edit', [\App\Http\Controllers\Admin\Uraian\DelapanKelDataController::class, 'edit'])->name('edit');
                Route::post('/', [\App\Http\Controllers\Admin\Uraian\DelapanKelDataController::class, 'store'])->name('store');
                Route::put('/{uraian8KelData}', [\App\Http\Controllers\Admin\Uraian\DelapanKelDataController::class, 'update'])->name('update');
                Route::delete('/{uraian8KelData}', [\App\Http\Controllers\Admin\Uraian\DelapanKelDataController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('bps')->name('bps.')->group(function () {
                Route::get('/{tabelBps?}', [\App\Http\Controllers\Admin\Uraian\BpsController::class, 'index'])->name('index');
                Route::get('/{tabelBps}/{uraianBps}/edit', [\App\Http\Controllers\Admin\Uraian\BpsController::class, 'edit'])->name('edit');
                Route::post('/', [\App\Http\Controllers\Admin\Uraian\BpsController::class, 'store'])->name('store');
                Route::put('/{uraianBps}', [\App\Http\Controllers\Admin\Uraian\BpsController::class, 'update'])->name('update');
                Route::delete('/{uraianBps}', [\App\Http\Controllers\Admin\Uraian\BpsController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('indikator')->name('indikator.')->group(function () {
                Route::get('/{tabelIndikator?}', [\App\Http\Controllers\Admin\Uraian\IndikatorController::class, 'index'])->name('index');
                Route::get('/{tabelIndikator}/{uraianIndikator}/edit', [\App\Http\Controllers\Admin\Uraian\IndikatorController::class, 'edit'])->name('edit');
                Route::post('/', [\App\Http\Controllers\Admin\Uraian\IndikatorController::class, 'store'])->name('store');
                Route::put('/{uraianIndikator}', [\App\Http\Controllers\Admin\Uraian\IndikatorController::class, 'update'])->name('update');
                Route::delete('/{uraianIndikator}', [\App\Http\Controllers\Admin\Uraian\IndikatorController::class, 'destroy'])->name('destroy');
            });
        });

        Route::name('delapankeldata.')->prefix('delapankeldata')->group(function () {
            Route::get('/{skpd_category?}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'index'])->name('index');
            Route::get('/skpd/{skpd}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'skpd'])->name('skpd');
            Route::get('/input/{tabel8KelData}/{skpd?}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'input'])->name('input');
            Route::get('/{uraian8KelData}/edit', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'edit'])->name('edit');
            Route::put('/', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'update'])->name('update');
            Route::delete('/{uraian8KelData}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'destroy'])->name('destroy');
            Route::put('/fitur/{fitur8KelData}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'updateFitur'])->name('update_fitur');
            Route::post('/files/{tabel8KelData}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'storeFile'])->name('files.store');
            Route::delete('/files/{file8KelData}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'destroyFile'])->name('files.destroy');
            Route::get('/files/download/{file8KelData}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'downloadFile'])->name('files.download');
            Route::put('/sumber_data/{uraian8KelData}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'updateSumberData']);
            Route::post('/tahun/{tabel8KelData}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'storeTahun'])->name('store_tahun');
            Route::delete('/tahun/{tabel8KelData}/{tahun}', [App\Http\Controllers\Admin\DelapanKelDataController::class, 'destroyTahun'])->name('destroy_tahun');
        });

        Route::name('indikator.')->prefix('indikator')->group(function () {
            Route::get('/{tabelIndikator?}', [App\Http\Controllers\Admin\IndikatorController::class, 'index'])->name('index');
            Route::get('/{uraianIndikator}/edit', [App\Http\Controllers\Admin\IndikatorController::class, 'edit'])->name('edit');
            Route::put('/', [App\Http\Controllers\Admin\IndikatorController::class, 'update'])->name('update');
            Route::delete('/{uraianIndikator}', [App\Http\Controllers\Admin\IndikatorController::class, 'destroy'])->name('destroy');
            Route::put('/fitur{fiturIndikator}', [App\Http\Controllers\Admin\IndikatorController::class, 'updateFitur'])->name('update_fitur');
            Route::post('/files/{tabelIndikator}', [App\Http\Controllers\Admin\IndikatorController::class, 'storeFile'])->name('files.store');
            Route::delete('/files/{fileIndikator}', [App\Http\Controllers\Admin\IndikatorController::class, 'destroyFile'])->name('files.destroy');
            Route::get('/files/download/{fileIndikator}', [App\Http\Controllers\Admin\IndikatorController::class, 'downloadFile'])->name('files.download');
            Route::post('/tahun/{tabelIndikator}', [App\Http\Controllers\Admin\IndikatorController::class, 'storeTahun'])->name('store_tahun');
            Route::delete('/tahun/{tabelIndikator}/{tahun}', [App\Http\Controllers\Admin\IndikatorController::class, 'destroyTahun'])->name('destroy_tahun');
        });

        Route::name('bps.')->prefix('bps')->group(function () {
            Route::get('/{tabelBps?}', [App\Http\Controllers\Admin\BpsController::class, 'index'])->name('index');
            Route::get('/{uraianBps}/edit', [App\Http\Controllers\Admin\BpsController::class, 'edit'])->name('edit');
            Route::put('/', [App\Http\Controllers\Admin\BpsController::class, 'update'])->name('update');
            Route::delete('/{uraianBps}', [App\Http\Controllers\Admin\BpsController::class, 'destroy'])->name('destroy');
            Route::put('/fitur/{fiturBps}', [App\Http\Controllers\Admin\BpsController::class, 'updateFitur'])->name('update_fitur');
            Route::post('/files/{tabelBps}', [App\Http\Controllers\Admin\BpsController::class, 'storeFile'])->name('files.store');
            Route::delete('/files/{fileBps}', [App\Http\Controllers\Admin\BpsController::class, 'destroyFile'])->name('files.destroy');
            Route::get('/files/download/{fileBps}', [App\Http\Controllers\Admin\BpsController::class, 'downloadFile'])->name('files.download');
            Route::put('/sumber_data/{uraianBps}', [App\Http\Controllers\Admin\BpsController::class, 'updateSumberData']);
            Route::post('/tahun/{tabelBps}', [App\Http\Controllers\Admin\BpsController::class, 'storeTahun'])->name('store_tahun');
            Route::delete('/tahun/{tabelBps}/{tahun}', [App\Http\Controllers\Admin\BpsController::class, 'destroyTahun'])->name('destroy_tahun');
        });

        Route::name('rpjmd.')->prefix('rpjmd')->group(function () {
            Route::get('/category/{skpd_category}', [App\Http\Controllers\Admin\RpjmdController::class, 'category'])->name('category');
            Route::get('/{tabelRpjmd?}', [App\Http\Controllers\Admin\RpjmdController::class, 'index'])->name('index');
            Route::get('/{uraianRpjmd}/edit', [App\Http\Controllers\Admin\RpjmdController::class, 'edit'])->name('edit');
            Route::put('/', [App\Http\Controllers\Admin\RpjmdController::class, 'update'])->name('update');
            Route::delete('/{uraianRpjmd}', [App\Http\Controllers\Admin\RpjmdController::class, 'destroy'])->name('destroy');
            Route::put('/fitur/{fiturRpjmd}', [App\Http\Controllers\Admin\RpjmdController::class, 'updateFitur'])->name('update_fitur');
            Route::post('/files/{tabelRpjmd}', [App\Http\Controllers\Admin\RpjmdController::class, 'storeFile'])->name('files.store');
            Route::delete('/files/{fileRpjmd}', [App\Http\Controllers\Admin\RpjmdController::class, 'destroyFile'])->name('files.destroy');
            Route::get('/files/download/{fileRpjmd}', [App\Http\Controllers\Admin\RpjmdController::class, 'downloadFile'])->name('files.download');
            Route::put('/sumber_data/{uraianRpjmd}', [App\Http\Controllers\Admin\RpjmdController::class, 'updateSumberData']);
            Route::post('/tahun/{tabelRpjmd}', [App\Http\Controllers\Admin\RpjmdController::class, 'storeTahun'])->name('store_tahun');
            Route::delete('/tahun/{tabelRpjmd}/{tahun}', [App\Http\Controllers\Admin\RpjmdController::class, 'destroyTahun'])->name('destroy_tahun');
        });
    });

    Route::middleware(['level:2'])->name('skpd.')->prefix('skpd')->group(function () {
        Route::get('/', App\Http\Controllers\Skpd\DashboardController::class)->name('dashboard');

        Route::prefix('delapankeldata')->name('delapankeldata.')->group(function () {
            Route::get('/{tabel8KelData?}', [App\Http\Controllers\Skpd\DelapanKelDataController::class, 'index'])->name('index');
            Route::get('/{uraian8KelData}/edit', [App\Http\Controllers\Skpd\DelapanKelDataController::class, 'edit'])->name('edit');
            Route::put('/', [App\Http\Controllers\Skpd\DelapanKelDataController::class, 'update'])->name('update');
            Route::delete('/{uraian8KelData}', [App\Http\Controllers\Skpd\DelapanKelDataController::class, 'destroy'])->name('destroy');
            Route::put('/fitur/{fitur8KelData}', [App\Http\Controllers\Skpd\DelapanKelDataController::class, 'updateFitur'])->name('update_fitur');
            Route::post('/files/{tabel8KelData}', [App\Http\Controllers\Skpd\DelapanKelDataController::class, 'storeFile'])->name('files.store');
            Route::delete('/files/{file8KelData}', [App\Http\Controllers\Skpd\DelapanKelDataController::class, 'destroyFile'])->name('files.destroy');
            Route::get('/files/download/{file8KelData}', [App\Http\Controllers\Skpd\DelapanKelDataController::class, 'downloadFile'])->name('files.download');
        });

        Route::prefix('rpjmd')->name('rpjmd.')->group(function () {
            Route::get('/{tabelRpjmd?}', [App\Http\Controllers\Skpd\RpjmdController::class, 'index'])->name('index');
            Route::get('/{uraianRpjmd}/edit', [App\Http\Controllers\Skpd\RpjmdController::class, 'edit'])->name('edit');
            Route::put('/', [App\Http\Controllers\Skpd\RpjmdController::class, 'update'])->name('update');
            Route::delete('/{uraianRpjmd}', [App\Http\Controllers\Skpd\RpjmdController::class, 'destroy'])->name('destroy');
            Route::put('/fitur/{fiturRpjmd}', [App\Http\Controllers\Skpd\RpjmdController::class, 'updateFitur'])->name('update_fitur');
            Route::post('/files/{tabelRpjmd}', [App\Http\Controllers\Skpd\RpjmdController::class, 'storeFile'])->name('files.store');
            Route::delete('/files/{fileRpjmd}', [App\Http\Controllers\Skpd\RpjmdController::class, 'destroyFile'])->name('files.destroy');
            Route::get('/files/download/{fileRpjmd}', [App\Http\Controllers\Skpd\RpjmdController::class, 'downloadFile'])->name('files.download');
        });
    });
});
