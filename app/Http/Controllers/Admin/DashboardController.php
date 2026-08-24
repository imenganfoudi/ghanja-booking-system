<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $stats = [
            'today' => Appointment::whereDate('appointment_date', $today)->count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'services' => Service::count(),
        ];

        $todayAppointments = Appointment::with('service')
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->get();

        return view('admin.dashboard', compact('stats', 'todayAppointments'));
    }
}