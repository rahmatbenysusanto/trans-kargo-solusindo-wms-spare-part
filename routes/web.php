<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\RMAController;
use App\Http\Controllers\StagingController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WriteOffController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'login')->name('login');
    Route::post('/', 'loginPost')->name('loginPost');
    Route::get('/logout', 'logout')->name('logout');
});

Route::get('/scan/{unique_id}', [InventoryController::class, 'scan'])->name('inventory.scan.public');

Route::middleware([AuthMiddleware::class])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/utilization-by-client', 'utilizationByClient')->name('utilizationByClient');
        Route::get('/rma-monitoring', 'rmaMonitoring')->name('rmaMonitoring');
        Route::get('/inbound-return', 'inboundReturn')->name('inboundReturn');
        Route::get('/stock-monitoring', 'stockMonitoring')->name('stockMonitoring');
    });

    Route::prefix('/inbound')->controller(InboundController::class)->group(function () {
        Route::prefix('/receiving')->group(function () {
            Route::get('/', 'receiving')->name('receiving');
            Route::get('/create', 'create')->name('receiving.create');
            Route::post('/store', 'store')->name('receiving.store');
            Route::get('/{id}', 'show')->name('receiving.show');
            Route::post('/approve', 'approve')->name('receiving.approve');
            Route::post('/cancel', 'cancel')->name('receiving.cancel');

            Route::prefix('/create')->group(function () {
                Route::get('/spare', 'createSpare')->name('receiving.create.spare');
                Route::get('/faulty', 'createFaulty')->name('receiving.create.faulty');
                Route::get('/rma', 'createRma')->name('receiving.create.rma');
                Route::get('/relokasi', 'createRelokasi')->name('receiving.create.relokasi');
                Route::get('/new-po', 'createNewPO')->name('receiving.create.new.po');

                Route::post('/store/relokasi', 'storeRelokasi')->name('receiving.store.relokasi');
                Route::post('/store/new-po', 'storeNewPO')->name('receiving.store.new-po');
                Route::post('/store/spare', 'storeSpare')->name('receiving.store.spare');
                Route::post('/store/faulty', 'storeFaulty')->name('receiving.store.faulty');
                Route::post('/store/rma', 'storeRma')->name('receiving.store.rma');
            });
        });

        Route::prefix('/put-away')->group(function () {
            Route::get('/', 'putAway')->name('receiving.put.away');
            Route::get('/show/{id}', 'showPutAway')->name('receiving.put.away.show');
            Route::get('/process/{id}', 'processPutAway')->name('receiving.put.away.process');
            Route::post('/update', 'updatePutAway')->name('receiving.put.away.update');
            Route::post('/cancel', 'cancelPutAway')->name('receiving.put.away.cancel');
        });
    });

    Route::prefix('/inventory')->controller(InventoryController::class)->group(function () {
        Route::get('/list', 'index')->name('inventory.index');
        Route::get('/list/pdf', 'exportPdf')->name('inventory.export.pdf');
        Route::get('/list/excel', 'exportExcel')->name('inventory.export.excel');
        Route::get('/list/{id}', 'show')->name('inventory.show');

        Route::get('/stock-movement', 'stockMovement')->name('inventory.stock.movement');
        Route::get('/product-movement', 'productMovementIndex')->name('inventory.product.movement');
        Route::get('/product-movement/process', 'productMovementProcess')->name('inventory.product.movement.process');
        Route::post('/product-movement/update', 'productMovementUpdate')->name('inventory.product.movement.update');
        Route::get('/product-summary', 'productSummary')->name('inventory.product.summary');
        Route::get('/product-summary/detail', 'productSummaryDetail')->name('inventory.product.summary.detail');
        Route::get('/stock-statement', 'stockStatement')->name('inventory.stock.statement');
    });

    Route::get('/inventory/cycle-count', [\App\Http\Controllers\CycleCountController::class, 'index'])->name('inventory.cycle-count');

    Route::prefix('/outbound')->controller(OutboundController::class)->group(function () {
        Route::get('/', 'index')->name('outbound.index');
        Route::prefix('/create')->group(function () {
            Route::get('/', 'create')->name('outbound.create');
            Route::get('/spare', 'createSpare')->name('outbound.create.spare');
            Route::get('/faulty', 'createFaulty')->name('outbound.create.faulty');
            Route::get('/rma', 'createRma')->name('outbound.create.rma');
            Route::get('/write-off', 'createWriteOff')->name('outbound.create.write-off');

            Route::post('/store/spare', 'storeSpare')->name('outbound.store.spare');
            Route::post('/store/faulty', 'storeFaulty')->name('outbound.store.faulty');
            Route::post('/store/rma', 'storeRma')->name('outbound.store.rma');
            Route::post('/store/write-off', 'storeWriteOff')->name('outbound.store.write-off');
        });

        Route::get('/get-inventory', 'getInventory')->name('outbound.get.inventory');
        Route::get('/{id}', 'show')->name('outbound.show');
        Route::get('/print/{id}', 'printPdf')->name('outbound.print');
        Route::post('/cancel', 'cancel')->name('outbound.cancel');
    });

    Route::prefix('/rma')->controller(RmaController::class)->group(function () {
        Route::get('/', 'index')->name('rma.index');
    });

    Route::prefix('/write-off')->controller(WriteOffController::class)->group(function () {
        Route::get('/', 'index')->name('write-off.index');
    });

    Route::prefix('/staging')->controller(StagingController::class)->group(function () {
        Route::get('/', 'index')->name('staging.index');
        Route::get('/search-available', 'searchAvailable')->name('staging.search-available');
        Route::post('/start', 'startStaging')->name('staging.start');
        Route::post('/finish', 'finishStaging')->name('staging.finish');
    });

    Route::prefix('/reporting')->controller(ReportingController::class)->group(function () {
        Route::get('/stock-on-hand', 'stockOnHand')->name('reporting.stock-on-hand');
        Route::get('/movement-history', 'movementHistory')->name('reporting.movement-history');
        Route::get('/utilization', 'utilizationReport')->name('reporting.utilization');
    });

    Route::prefix('/invoice')->controller(InvoiceController::class)->group(function () {
        Route::get('/', 'index')->name('invoice.index');
        Route::get('/create', 'create')->name('invoice.create');
        Route::post('/store', 'store')->name('invoice.store');
        Route::get('/export-excel', 'exportExcel')->name('invoice.export-excel');
        Route::get('/print/{id}', 'printPdf')->name('invoice.print');
        Route::get('/search-reference', 'searchReference')->name('invoice.search-reference');
        Route::delete('/{id}', 'destroy')->name('invoice.destroy');
    });

    Route::prefix('/storage')->controller(StorageController::class)->group(function () {
        Route::prefix('/zone')->group(function () {
            Route::get('/', 'zone')->name('storage.zone');
            Route::post('/store', 'zoneStore')->name('storage.zone.store');
            Route::post('/update', 'zoneUpdate')->name('storage.zone.update');
            Route::get('/destroy/{id}', 'zoneDestroy')->name('storage.zone.destroy');
        });

        Route::prefix('/rak')->group(function () {
            Route::get('/', 'rak')->name('storage.rak');
            Route::post('/store', 'rakStore')->name('storage.rak.store');
            Route::post('/update', 'rakUpdate')->name('storage.rak.update');
            Route::get('/destroy/{id}', 'rakDestroy')->name('storage.rak.destroy');
            Route::get('/find', 'rakFind')->name('storage.rak.find');
        });

        Route::prefix('/bin')->group(function () {
            Route::get('/', 'bin')->name('storage.bin');
            Route::post('/store', 'binStore')->name('storage.bin.store');
            Route::post('/update', 'binUpdate')->name('storage.bin.update');
            Route::get('/destroy/{id}', 'binDestroy')->name('storage.bin.destroy');
            Route::get('/find', 'binFind')->name('storage.bin.find');
        });

        Route::prefix('/level')->group(function () {
            Route::get('/', 'level')->name('storage.level');
            Route::post('/store', 'levelStore')->name('storage.level.store');
            Route::post('/update', 'levelUpdate')->name('storage.level.update');
            Route::get('/destroy/{id}', 'levelDestroy')->name('storage.level.destroy');
            Route::get('/find', 'levelFind')->name('storage.level.find');
        });
    });

    Route::prefix('/user')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('user.index');
        Route::post('/store', 'store')->name('user.store');
        Route::post('/update', 'update')->name('user.update');
        Route::get('/destroy/{id}', 'destroy')->name('user.destroy');

        Route::prefix('/menu')->controller(MenuController::class)->group(function () {
            Route::get('/', 'index')->name('menu.index');
            Route::post('/store', 'store')->name('menu.store');
            Route::post('/update', 'update')->name('menu.update');
            Route::get('/destroy/{id}', 'destroy')->name('menu.destroy');
            Route::get('/user/{userId}', 'getUserMenus')->name('menu.user');
            Route::post('/user/toggle', 'toggleUserMenu')->name('menu.user.toggle');
        });
    });

    Route::prefix('/brand')->controller(BrandController::class)->group(function () {
        Route::get('/', 'index')->name('brand.index');
        Route::post('/store', 'store')->name('brand.store');
        Route::post('/update', 'update')->name('brand.update');
    });

    Route::prefix('/product')->controller(ProductController::class)->group(function () {
        Route::prefix('/group')->group(function () {
            Route::get('/', 'index')->name('product.group.index');
            Route::post('/store', 'store')->name('product.group.store');
            Route::post('/update', 'update')->name('product.group.update');
        });
    });

    Route::prefix('/client')->controller(ClientController::class)->group(function () {
        Route::get('/', 'index')->name('client.index');
        Route::post('/store', 'store')->name('client.store');
        Route::post('/update', 'update')->name('client.update');
    });
});
