<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\GoogleCalendarSetting;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;

class GoogleCalendarService
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $setting;

    public function __construct()
    {
        $this->clientId = config('services.google.client_id');
        $this->clientSecret = config('services.google.client_secret');
        $this->redirectUri = config('services.google.redirect_uri');
    }

    /**
     * Get the auth URL for OAuth flow
     */
    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/userinfo.email',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    }

    /**
     * Handle the OAuth callback and save tokens
     */
    public function handleCallback(string $code, int $userId)
    {
        try {
            // Exchange code for tokens
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code'
            ]);

            if (!$response->successful()) {
                throw new Exception('Error exchanging code for token: ' . $response->body());
            }

            $data = $response->json();
            $accessToken = $data['access_token'];
            $refreshToken = $data['refresh_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 3600;

            // Get user email from Google
            $userInfoResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if (!$userInfoResponse->successful()) {
                throw new Exception('Error getting user info');
            }

            $userInfo = $userInfoResponse->json();

            // Save or update settings
            GoogleCalendarSetting::updateOrCreate(
                ['user_id' => $userId],
                [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_expires_at' => now()->addSeconds($expiresIn),
                    'google_email' => $userInfo['email'],
                    'sync_enabled' => true
                ]
            );

            return true;
        } catch (Exception $e) {
            \Log::error('Google Calendar OAuth error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load settings and refresh token if needed
     */
    private function loadSettings(int $userId): bool
    {
        $this->setting = GoogleCalendarSetting::where('user_id', $userId)->first();

        if (!$this->setting || !$this->setting->sync_enabled) {
            return false;
        }

        // Check if token is expired and refresh if needed
        if ($this->setting->token_expires_at->isPast()) {
            $this->refreshAccessToken();
        }

        return true;
    }

    /**
     * Refresh the access token
     */
    private function refreshAccessToken()
    {
        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->setting->refresh_token,
                'grant_type' => 'refresh_token'
            ]);

            if (!$response->successful()) {
                throw new Exception('Error refreshing token: ' . $response->body());
            }

            $data = $response->json();

            $this->setting->update([
                'access_token' => $data['access_token'],
                'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600)
            ]);
        } catch (Exception $e) {
            \Log::error('Error refreshing Google Calendar token: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a calendar event from an appointment
     */
    public function createEvent(Appointment $appointment): ?string
    {
        try {
            if (!$this->loadSettings($appointment->user_id)) {
                return null;
            }

            $startDateTime = Carbon::parse($appointment->appointment_datetime);
            $endDateTime = $startDateTime->copy()->addMinutes($appointment->duration_minutes);

            $event = [
                'summary' => 'Cita: ' . $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                'description' => $this->buildEventDescription($appointment),
                'start' => [
                    'dateTime' => $startDateTime->toIso8601String(),
                    'timeZone' => 'America/Bogota',
                ],
                'end' => [
                    'dateTime' => $endDateTime->toIso8601String(),
                    'timeZone' => 'America/Bogota',
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'popup', 'minutes' => 1440], // 1 day before
                        ['method' => 'popup', 'minutes' => 60],   // 1 hour before
                    ],
                ],
            ];

            $response = Http::withToken($this->setting->access_token)
                ->post("https://www.googleapis.com/calendar/v3/calendars/{$this->setting->calendar_id}/events", $event);

            if (!$response->successful()) {
                \Log::error('Error creating Google Calendar event: ' . $response->body());
                return null;
            }

            $createdEvent = $response->json();
            return $createdEvent['id'];
        } catch (Exception $e) {
            \Log::error('Error creating Google Calendar event: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a calendar event
     */
    public function updateEvent(Appointment $appointment): bool
    {
        try {
            if (!$this->loadSettings($appointment->user_id) || !$appointment->google_calendar_event_id) {
                return false;
            }

            $startDateTime = Carbon::parse($appointment->appointment_datetime);
            $endDateTime = $startDateTime->copy()->addMinutes($appointment->duration_minutes);

            $summary = 'Cita: ' . $appointment->patient->first_name . ' ' . $appointment->patient->last_name;
            if ($appointment->status === 'completed') {
                $summary = '[COMPLETADO] ' . $summary;
            } elseif ($appointment->status === 'cancelled') {
                $summary = '[CANCELADO] ' . $summary;
            }

            $event = [
                'summary' => $summary,
                'description' => $this->buildEventDescription($appointment),
                'start' => [
                    'dateTime' => $startDateTime->toIso8601String(),
                    'timeZone' => 'America/Bogota',
                ],
                'end' => [
                    'dateTime' => $endDateTime->toIso8601String(),
                    'timeZone' => 'America/Bogota',
                ],
            ];

            $response = Http::withToken($this->setting->access_token)
                ->put("https://www.googleapis.com/calendar/v3/calendars/{$this->setting->calendar_id}/events/{$appointment->google_calendar_event_id}", $event);

            if (!$response->successful()) {
                \Log::error('Error updating Google Calendar event: ' . $response->body());
                return false;
            }

            return true;
        } catch (Exception $e) {
            \Log::error('Error updating Google Calendar event: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a calendar event
     */
    public function deleteEvent(string $eventId, int $userId): bool
    {
        try {
            if (!$this->loadSettings($userId)) {
                return false;
            }

            $response = Http::withToken($this->setting->access_token)
                ->delete("https://www.googleapis.com/calendar/v3/calendars/{$this->setting->calendar_id}/events/{$eventId}");

            if (!$response->successful() && $response->status() !== 404) {
                \Log::error('Error deleting Google Calendar event: ' . $response->body());
                return false;
            }

            return true;
        } catch (Exception $e) {
            \Log::error('Error deleting Google Calendar event: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Build event description from appointment data
     */
    private function buildEventDescription(Appointment $appointment): string
    {
        $procedures = collect($appointment->planned_procedures)->pluck('name')->implode(', ');

        $description = "Paciente: {$appointment->patient->first_name} {$appointment->patient->last_name}\n";
        $description .= "Doctor: {$appointment->referrer_name}\n";
        $description .= "Teléfono: {$appointment->patient->cellphone}\n";
        $description .= "Procedimientos: {$procedures}\n";

        if ($appointment->notes) {
            $description .= "\nNotas:\n{$appointment->notes}";
        }

        return $description;
    }

    /**
     * Get connection status
     */
    public static function getConnectionStatus(int $userId): array
    {
        $setting = GoogleCalendarSetting::where('user_id', $userId)->first();

        return [
            'connected' => $setting && $setting->sync_enabled,
            'email' => $setting->google_email ?? null,
            'sync_enabled' => $setting->sync_enabled ?? false
        ];
    }

    /**
     * Disconnect Google Calendar
     */
    public static function disconnect(int $userId): bool
    {
        $setting = GoogleCalendarSetting::where('user_id', $userId)->first();

        if ($setting) {
            $setting->update(['sync_enabled' => false]);
            return true;
        }

        return false;
    }
}
