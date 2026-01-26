<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RMAController;
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

            Route::prefix('/create')->group(function () {
                Route::get('/spare', 'createSpare')->name('receiving.create.spare');
                Route::get('/faulty', 'createFaulty')->name('receiving.create.faulty');
                Route::get('/rma', 'createRma')->name('receiving.create.rma');
                Route::get('/new-po', 'createNewPO')->name('receiving.create.new.po');
                Route::post('/store-new-po', 'storeNewPO')->name('receiving.store.new.po');
            });
        });

        Route::prefix('/put-away')->group(function () {
            Route::get('/', 'putAway')->name('receiving.put.away');
        });
    });

    Route::prefix('/inventory')->controller(InventoryController::class)->group(function () {
        Route::get('/list', 'index')->name('inventory.index');

        Route::get('/stock-movement', 'stockMovement')->name('inventory.stock.movement');
    });

    Route::prefix('/outbound')->controller(OutboundController::class)->group(function () {
        Route::get('/', 'index')->name('outbound.index');
    });

    Route::prefix('/rma')->controller(RmaController::class)->group(function () {
        Route::get('/', 'index')->name('rma.index');
    });

    Route::prefix('/write-off')->controller(WriteOffController::class)->group(function () {
        Route::get('/', 'index')->name('write-off.index');
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
        Route::get('/create', 'create')->name('user.create');
        Route::post('/store', 'store')->name('user.store');
        Route::get('/edit', 'edit')->name('user.edit');
        Route::post('/update', 'update')->name('user.update');

        Route::prefix('/menu')->group(function () {
            Route::get('/', 'menu')->name('user.menu');
            Route::post('/store', 'menuStore')->name('user.menu.store');
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
