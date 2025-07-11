<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Vehicule;
use App\Models\Comptes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservationController extends Controller
{
    // public function store(Request $request)
    // {
    //     Log::info('Données reçues:', $request->all());
        
    //     // Validation des données
    //     $validated = $request->validate([
    //         'date_debut' => 'required|date|after_or_equal:today',
    //         'date_fin' => 'required|date|after:date_debut',
    //         'vehicule_id' => 'required|exists:vehicules,num_vil',
    //     ]);

    //     // Calcul du montant
    //     $vehicule = Vehicule::findOrFail($request->vehicule_id);
    //     $start = Carbon::parse($request->date_debut);
    //     $end = Carbon::parse($request->date_fin);
    //     $jours = $end->diffInDays($start) + 1; // +1 pour inclure le premier jour
    //     $montant = $vehicule->prix * $jours;

    //     // Création de la réservation
    //     $reservation = Reservation::create([
    //         'date_debut' => $request->date_debut,
    //         'date_fin' => $request->date_fin,
    //         'montant' => $montant,
    //         'statut' => 'en_attente',
    //         'moyen_reserv' => 'en_ligne',
    //     ]);

    //     // Lier au véhicule (simplifié)
    //     $reservation->vehicules()->attach($request->vehicule_id);
        
    //     // Lier le compte utilisateur via la relation many-to-many
    //     $reservation->comptes()->attach(Auth::user()->id_compte, [
    //         'date_reserv2' => now(), // Utilisez le nom exact de colonne de votre table pivot
    //         'created_at' => now(),
    //         'updated_at' => now()
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'reservation' => $reservation,
    //         'message' => 'Réservation enregistrée avec succès',
    //         'total' => $montant,
    //         'days' => $jours
    //     ]);
    // }

  // Dans ReservationController.php
public function selectVehicle(Request $request)
{
    try {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicules,num_vil',
            'compte_id' => 'required|exists:comptes,id_compte'
        ]);

        $vehicle = Vehicule::findOrFail($request->vehicle_id);
        
        return response()->json([
            'success' => true,
            'vehicle' => $vehicle
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 400);
    }
}

    public function calculMontant(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicules,num_vil',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut'
        ]);

        $vehicle = Vehicule::findOrFail($request->vehicle_id);
        
        $startDate = Carbon::parse($request->date_debut);
        $endDate = Carbon::parse($request->date_fin);
        
        $days = $startDate->diffInDays($endDate) + 1; // +1 pour inclure le dernier jour
        $total = $days * $vehicle->prix;

        return response()->json([
            'success' => true,
            'vehicle' => $vehicle,
            'days' => $days,
            'total' => $total,
            'dates' => [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y')
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicules,num_vil',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut'
        ]);

        $vehicle = Vehicule::findOrFail($request->vehicle_id);
        
        $startDate = Carbon::parse($request->date_debut);
        $endDate = Carbon::parse($request->date_fin);
        
        $days = $startDate->diffInDays($endDate) + 1;
        $total = $days * $vehicle->prix;

        $reservation = Reservation::create([
            'user_id' => auth()->id(),
            'vehicule_id' => $request->vehicle_id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
            'montant' => $total,
            'statut' => 'en_attente'
        ]);

        return response()->json([
            'success' => true,
            'reservation' => $reservation,
            'message' => 'Réservation confirmée avec succès'
        ]);
    }
}
