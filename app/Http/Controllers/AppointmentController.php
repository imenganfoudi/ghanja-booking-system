<?php

namespace App\Http\Controllers;

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
     * Affiche le formulaire de réservation.
     */
    public function index()
    {
        $services = Service::where('is_active', true)->get();
        $staff = Staff::where('is_active', true)->get();

        return view('front.book-appointment', compact('services', 'staff'));
    }

    /**
     * Retourne les créneaux disponibles pour un service / une date / (optionnel) un employé.
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
     * Enregistre une nouvelle réservation.
     *
     * Utilise une transaction + verrouillage des lignes (lockForUpdate) pour
     * empêcher qu'un double-clic ou deux requêtes simultanées créent
     * deux rendez-vous sur le même créneau (race condition).
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
            // Format strict "HH:MM" pour éviter qu'un texte invalide fasse planter Carbon::parse()
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
                    // Verrouille les lignes concernées jusqu'à la fin de la transaction
                    // (nécessite MySQL/PostgreSQL — non supporté par SQLite)
                    ->lockForUpdate();

                if (!empty($validated['staff_id'])) {
                    $conflictQuery->where('staff_id', $validated['staff_id']);
                }

                if ($conflictQuery->exists()) {
                    // On sort de la transaction proprement avec une exception dédiée
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
                return back()
                    ->withErrors(['start_time' => 'هذا الوقت تحجز، إختار وقت آخر.'])
                    ->withInput();
            }

            throw $e;
        }

        // --- Envoi des emails (version simple, sans Mailable classes) ---
        // Protégé par try/catch : si le mail échoue (SMTP mal configuré, etc.),
        // on ne bloque pas la réservation qui est déjà enregistrée en base.
        try {
            // Email au client
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

            // Email à l'admin
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
            report($e); // log l'erreur sans casser le flux utilisateur
        }

        // Redirection vers une URL signée : personne ne peut deviner/forger
        // le lien de confirmation d'un autre client (protection anti-IDOR).
        return redirect()
            ->signedRoute('booking.confirmation', ['appointment' => $appointment->id])
            ->with('success', 'تم إرسال طلب الحجز بنجاح!');
    }

    /**
     * Affiche la page de confirmation d'un rendez-vous.
     *
     * Protégée par une signature Laravel (middleware 'signed' sur la route +
     * vérification explicite ici en défense en profondeur) : seul le lien
     * généré juste après la réservation est valide, on ne peut pas deviner
     * l'URL d'un autre client en changeant l'ID.
     */
    public function confirmation(Request $request, Appointment $appointment)
    {
        abort_unless($request->hasValidSignature(), 403);

        return view('front.booking-confirmation', compact('appointment'));
    }
}