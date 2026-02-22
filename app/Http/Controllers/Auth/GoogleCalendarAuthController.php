<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GoogleCalendarAuthController extends Controller
{
    protected $calendarService;

    public function __construct()
    {
        $this->calendarService = new GoogleCalendarService();
    }

    /**
     * Redirect to Google OAuth page
     */
    public function redirectToGoogle(): JsonResponse
    {
        try {
            $authUrl = $this->calendarService->getAuthUrl();

            return response()->json([
                'auth_url' => $authUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generando URL de autenticación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle the OAuth callback
     */
    public function handleCallback(Request $request): JsonResponse
    {
        try {
            $code = $request->query('code');

            if (!$code) {
                return response()->json([
                    'message' => 'Código de autorización no recibido'
                ], 400);
            }

            $this->calendarService->handleCallback($code, auth()->id());

            return response()->json([
                'message' => 'Google Calendar conectado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al conectar con Google Calendar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get connection status
     */
    public function getStatus(): JsonResponse
    {
        try {
            $status = GoogleCalendarService::getConnectionStatus(auth()->id());

            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estado de conexión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnect(): JsonResponse
    {
        try {
            GoogleCalendarService::disconnect(auth()->id());

            return response()->json([
                'message' => 'Google Calendar desconectado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al desconectar Google Calendar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
