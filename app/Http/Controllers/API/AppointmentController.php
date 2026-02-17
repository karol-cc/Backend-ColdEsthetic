<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    protected $calendarService;

    public function __construct()
    {
        $this->calendarService = new GoogleCalendarService();
    }

    /**
     * List all appointments with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Appointment::with(['patient', 'user', 'procedure']);

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('appointment_datetime', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('appointment_datetime', '<=', $request->end_date);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by referrer
            if ($request->has('referrer_name')) {
                $query->where('referrer_name', $request->referrer_name);
            }

            // Order by appointment date
            $appointments = $query->orderBy('appointment_datetime', 'asc')->get();

            return response()->json($appointments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener citas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming appointments (next 30 days)
     */
    public function upcoming(): JsonResponse
    {
        try {
            $appointments = Appointment::with(['patient', 'user'])
                ->where('appointment_datetime', '>=', now())
                ->where('appointment_datetime', '<=', now()->addDays(30))
                ->whereIn('status', ['pending', 'confirmed'])
                ->orderBy('appointment_datetime', 'asc')
                ->get();

            return response()->json($appointments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener próximas citas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single appointment
     */
    public function show(Appointment $appointment): JsonResponse
    {
        try {
            $appointment->load(['patient', 'user', 'procedure']);

            return response()->json($appointment);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la cita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new appointment
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = Appointment::create([
                'user_id' => auth()->id(),
                'patient_id' => $request->patient_id,
                'referrer_name' => $request->referrer_name,
                'appointment_datetime' => $request->appointment_datetime,
                'duration_minutes' => $request->duration_minutes,
                'planned_procedures' => $request->planned_procedures,
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            // Try to create Google Calendar event
            $eventId = $this->calendarService->createEvent($appointment);
            if ($eventId) {
                $appointment->update(['google_calendar_event_id' => $eventId]);
            }

            $appointment->load(['patient', 'user']);

            return response()->json([
                'message' => 'Cita creada exitosamente',
                'appointment' => $appointment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la cita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an appointment
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        try {
            $appointment->update($request->validated());

            // Update Google Calendar event if it exists
            if ($appointment->google_calendar_event_id) {
                $this->calendarService->updateEvent($appointment);
            }

            $appointment->load(['patient', 'user']);

            return response()->json([
                'message' => 'Cita actualizada exitosamente',
                'appointment' => $appointment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la cita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel (delete) an appointment
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        try {
            // Delete from Google Calendar if synced
            if ($appointment->google_calendar_event_id) {
                $this->calendarService->deleteEvent(
                    $appointment->google_calendar_event_id,
                    $appointment->user_id
                );
            }

            // Mark as cancelled instead of deleting
            $appointment->update(['status' => 'cancelled']);

            return response()->json([
                'message' => 'Cita cancelada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cancelar la cita',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert appointment to completed procedure
     */
    public function completeAppointment(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            // Validate that procedure data is provided
            $request->validate([
                'procedure_items' => 'required|array|min:1',
                'procedure_items.*.item_name' => 'required|string',
                'procedure_items.*.price' => 'required|numeric|min:0',
                'procedure_items.*.meta' => 'nullable|array',
                'notes' => 'nullable|string'
            ]);

            // Calculate total amount
            $totalAmount = collect($request->procedure_items)->sum('price');

            // Create medical evaluation if it doesn't exist
            $medicalEvaluation = $appointment->patient->medicalEvaluations()->latest()->first();

            if (!$medicalEvaluation) {
                return response()->json([
                    'message' => 'El paciente debe tener una evaluación médica antes de completar la cita'
                ], 400);
            }

            // Create procedure
            $procedure = $medicalEvaluation->procedures()->create([
                'procedure_date' => $appointment->appointment_datetime->format('Y-m-d'),
                'total_amount' => $totalAmount,
                'notes' => $request->notes ?? $appointment->notes
            ]);

            // Create procedure items
            foreach ($request->procedure_items as $item) {
                $procedure->procedureItems()->create([
                    'item_name' => $item['item_name'],
                    'price' => $item['price'],
                    'meta' => $item['meta'] ?? null
                ]);
            }

            // Link procedure to appointment and mark as completed
            $appointment->update([
                'procedure_id' => $procedure->id,
                'status' => 'completed'
            ]);

            // Update Google Calendar event
            if ($appointment->google_calendar_event_id) {
                $this->calendarService->updateEvent($appointment);
            }

            $appointment->load(['patient', 'user', 'procedure.procedureItems']);

            return response()->json([
                'message' => 'Cita marcada como completada y procedimiento registrado',
                'appointment' => $appointment,
                'procedure' => $procedure
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al completar la cita',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
