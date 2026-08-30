<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    /**
     * Liste tous les rendez-vous (pour l'admin dashboard).
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['service', 'staff'])->orderBy('appointment_date', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(15));
    }

    /**
     * Détail d'un rendez-vous.
     */
    public function show(Appointment $appointment)
    {
        return response()->json($appointment->load(['service', 'staff']));
    }

    /**
     * Liste des services + staff actifs (pour le formulaire de réservation).
     */
    public function bookingData()
    {
        return response()->json([
            'services' => Service::where('is_active', true)->get(),
            'staff' => Staff::where('is_active', true)->get(),
        ]);
    }

    /**
     * Créneaux disponibles pour un service / une date / (optionnel) un employé.
     */
    public function availableSlots(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'staff_id' => 'nullable|exists:staff,id',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $duration = $service->duration_minutes;

        $openTime = Carbon::parse($validated['date'].' 09:00');
        $closeTime = Carbon::parse($validated['date'].' 18:00');

        $bookedQuery = Appointment::whereDate('appointment_date', $validated['date'])
            ->whereIn('status', ['pending', 'confirmed']);

        if (!empty($validated['staff_id'])) {
            $bookedQuery->where('staff_id', $validated['staff_id']);
        }

        $booked = $bookedQuery->get(['start_time', 'end_time']);

        $slots = [];
        $cursor = $openTime->copy();

        while ($cursor->copy()->addMinutes($duration)->lte($closeTime)) {
            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addMinutes($duration);

            $overlaps = $booked->contains(function ($b) use ($slotStart, $slotEnd, $validated) {
                $bStart = Carbon::parse($validated['date'].' '.$b->start_time);
                $bEnd = Carbon::parse($validated['date'].' '.$b->end_time);

                return $slotStart->lt($bEnd) && $slotEnd->gt($bStart);
            });

            if (!$overlaps) {
                $slots[] = $slotStart->format('H:i');
            }

            $cursor->addMinutes($duration);
        }

        return response()->json(['slots' => $slots]);
    }

    /**
     * Enregistre une nouvelle réservation (API version).
     * Même logique que la version web : transaction + lockForUpdate
     * pour empêcher les doubles réservations sur le même créneau.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:staff,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:30',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        $start = Carbon::parse($validated['appointment_date'].' '.$validated['start_time']);
        $end = $start->copy()->addMinutes($service->duration_minutes);

        try {
            $appointment = DB::transaction(function () use ($validated, $start, $end) {

                $conflictQuery = Appointment::whereDate('appointment_date', $validated['appointment_date'])
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->where(function ($q) use ($start, $end) {
                        $q->where('start_time', '<', $end->format('H:i:s'))
                          ->where('end_time', '>', $start->format('H:i:s'));
                    })
                    ->lockForUpdate();

                if (!empty($validated['staff_id'])) {
                    $conflictQuery->where('staff_id', $validated['staff_id']);
                }

                if ($conflictQuery->exists()) {
                    throw new \RuntimeException('SLOT_CONFLICT');
                }

                return Appointment::create([
                    'service_id' => $validated['service_id'],
                    'staff_id' => $validated['staff_id'] ?? null,
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'],
                    'appointment_date' => $validated['appointment_date'],
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $end->format('H:i:s'),
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                ]);
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'SLOT_CONFLICT') {
                return response()->json([
                    'message' => 'Ce créneau vient d\'être réservé par quelqu\'un d\'autre.',
                ], 409);
            }

            throw $e;
        }

        try {
            Mail::raw(
                "Bonjour {$appointment->customer_name},\n\n".
                "Votre demande de rendez-vous a bien été reçue.\n".
                "Service : {$service->name}\n".
                "Date : {$start->format('d/m/Y')}\n".
                "Heure : {$start->format('H:i')}\n\n".
                "Nous vous contacterons pour confirmer.\n\nGhanja",
                function ($message) use ($appointment) {
                    $message->to($appointment->customer_email)
                        ->subject('Confirmation de votre rendez-vous — Ghanja');
                }
            );

            $adminEmail = env('ADMIN_EMAIL');
            if ($adminEmail) {
                Mail::raw(
                    "Nouvelle réservation :\n\n".
                    "Client : {$appointment->customer_name}\n".
                    "Téléphone : {$appointment->customer_phone}\n".
                    "Email : {$appointment->customer_email}\n".
                    "Service : {$service->name}\n".
                    "Date : {$start->format('d/m/Y')}\n".
                    "Heure : {$start->format('H:i')}",
                    function ($message) use ($adminEmail) {
                        $message->to($adminEmail)
                            ->subject('Nouvelle demande de rendez-vous');
                    }
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Rendez-vous enregistré avec succès.',
            'appointment' => $appointment->load(['service', 'staff']),
        ], 201);
    }

    /**
     * Met à jour le statut d'un rendez-vous (admin).
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $appointment->update($validated);

        return response()->json($appointment->load(['service', 'staff']));
    }

    /**
     * Supprime un rendez-vous.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json(null, 204);
    }
}