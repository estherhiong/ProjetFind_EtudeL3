<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\VehiculeImageController;
use App\Http\Controllers\VehiculeController;
use App\Http\Controllers\ReservationController;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Authentification
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Gestion des comptes
    Route::get('/compte/current', function (Request $request) {
        return $request->user();
    });
    Route::post('/registerAdmin', [CompteController::class, 'registerAdmin']);
    Route::get('/compte', [CompteController::class, 'index']);
    Route::get('/compte/search', [CompteController::class, 'search']);
    Route::put('/compte/{id}', [CompteController::class, 'update']);
    Route::delete('/compte/{id}', [CompteController::class, 'destroy']);
    
    // Gestion des véhicules
    Route::get('/vehicules', [VehiculeController::class, 'index']);
    Route::get('/vehicules/client', [VehiculeController::class, 'showListeVehicule']);
    Route::get('/vehicules/{id}', [VehiculeController::class, 'show']);
    Route::post('/vehicules', [VehiculeController::class, 'store']);
    Route::put('/vehicules/{id}', [VehiculeController::class, 'update']);
    Route::delete('/vehicules/{id}', [VehiculeController::class, 'destroy']);
    Route::get('/vehicules/search', [VehiculeController::class, 'searchByChassis']);
    Route::get('/vehicules/chassis-list', [VehiculeController::class, 'getChassisList']);
    
    // Gestion des images
    Route::get('/vehicules/{vehicule}/images', [VehiculeImageController::class, 'index']);
    Route::post('/vehicules/{vehicule}/images', [VehiculeImageController::class, 'store']);
    Route::delete('/vehicules/{vehicule}/images', [VehiculeImageController::class, 'destroyAll']);
    Route::delete('/images/{image}', [VehiculeImageController::class, 'destroy']);
    Route::put('/vehicules/{vehicule}/images/order', [VehiculeImageController::class, 'updateOrder']);

    //reservation
    //Route::post('/reservations', [ReservationController::class, 'store']);
    Route::post('/reservation/vehicle-montant', [ReservationController::class, 'calculMontant']);
    Route::post('/reservation', [ReservationController::class, 'store']);
    Route::post('/reservation/select-vehicle', [ReservationController::class, 'selectVehicle']);
    //Route::post('/reservation/select-vehicle', [ReservationController::class, 'selectVehicle']);

});