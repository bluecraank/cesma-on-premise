<?php

use App\Http\Controllers\OverviewController;
use Illuminate\Support\Facades\Route;

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
// Start, Switchübersicht
Route::get('/', [OverviewController::class, 'index']);
Route::redirect('/switch', '/', 301);

// VLAN Übersicht
Route::get('/vlans', [OverviewController::class, 'index']);

// Trunk Übersicht
Route::get('/trunks', [OverviewController::class, 'index']);

// Benutzereinstellungen
Route::get('/user-settings', [OverviewController::class, 'index']);

// Systemeinstellungen
Route::get('/system-settings', [OverviewController::class, 'index']);

// Log, Protokoll
Route::get('/logging', [OverviewController::class, 'index']);

// Standorte, Gebäude
Route::get('/locations', [OverviewController::class, 'index']);

// SSH Befehle ausführen
Route::get('/perform-ssh', [OverviewController::class, 'index']);
