<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Statistiques globales pour le dashboard admin.
     */
    public function stats()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        return response()->json([
            // Compteurs généraux
            'total_appointments' => Appointment::count(),
            'appointments_today' => Appointment::whereDate('appointment_date', $today)->count(),
            'appointments_this_month' => Appointment::where('appointment_date', '>=', $startOfMonth)->count(),

            // Répartition par statut
            'by_status' => Appointment::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),

            // Revenu estimé (rendez-vous confirmés/complétés) x prix du service
            'estimated_revenue' => Appointment::join('services', 'appointments.service_id', '=', 'services.id')
                ->whereIn('appointments.status', ['confirmed', 'completed'])
                ->sum('services.price'),

            // Top 5 services les plus réservés
            'top_services' => Appointment::select('services.name', DB::raw('count(*) as bookings'))
                ->join('services', 'appointments.service_id', '=', 'services.id')
                ->groupBy('services.name')
                ->orderByDesc('bookings')
                ->limit(5)
                ->get(),

            // Réservations des 7 derniers jours (pour un graphique en courbe)
            'last_7_days' => $this->last7DaysBookings(),
        ]);
    }

    private function last7DaysBookings(): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'count' => Appointment::whereDate('appointment_date', $date)->count(),
            ];
        }

        return $data;
    }
}