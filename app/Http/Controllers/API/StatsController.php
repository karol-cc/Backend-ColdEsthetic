<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Procedure;
use App\Models\ProcedureItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    /**
     * KPIs generales
     */
        public function summary()
    {
        $now = Carbon::now();

        // Mes actual
        $thisMonthIncome = Procedure::whereMonth('procedure_date', $now->month)
            ->whereYear('procedure_date', $now->year)
            ->sum('total_amount');

        $thisMonthPatients = Patient::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $thisMonthSessions = Procedure::whereMonth('procedure_date', $now->month)
            ->whereYear('procedure_date', $now->year)
            ->count();

        $thisMonthProcedures = ProcedureItem::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Mes anterior
        $lastMonth = $now->copy()->subMonth();

        $lastMonthIncome = Procedure::whereMonth('procedure_date', $lastMonth->month)
            ->whereYear('procedure_date', $lastMonth->year)
            ->sum('total_amount');

        $lastMonthPatients = Patient::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        $lastMonthSessions = Procedure::whereMonth('procedure_date', $lastMonth->month)
            ->whereYear('procedure_date', $lastMonth->year)
            ->count();

        $lastMonthProcedures = ProcedureItem::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        // Variaciones porcentuales
        $incomeVariation = $lastMonthIncome > 0
            ? round((($thisMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100, 2)
            : null;

        $patientsVariation = $lastMonthPatients > 0
            ? round((($thisMonthPatients - $lastMonthPatients) / $lastMonthPatients) * 100, 2)
            : null;

        $sessionsVariation = $lastMonthSessions > 0
            ? round((($thisMonthSessions - $lastMonthSessions) / $lastMonthSessions) * 100, 2)
            : null;

        $proceduresVariation = $lastMonthProcedures > 0
            ? round((($thisMonthProcedures - $lastMonthProcedures) / $lastMonthProcedures) * 100, 2)
            : null;

        return response()->json([
            'total_patients' => Patient::count(),
            'total_sessions' => Procedure::count(),
            'total_procedures' => ProcedureItem::count(),
            'total_income' => Procedure::sum('total_amount'),

            'this_month_income' => $thisMonthIncome,
            'last_month_income' => $lastMonthIncome,
            'income_variation' => $incomeVariation,

            'this_month_patients' => $thisMonthPatients,
            'last_month_patients' => $lastMonthPatients,
            'patients_variation' => $patientsVariation,

            'this_month_sessions' => $thisMonthSessions,
            'last_month_sessions' => $lastMonthSessions,
            'sessions_variation' => $sessionsVariation,

            'this_month_procedures' => $thisMonthProcedures,
            'last_month_procedures' => $lastMonthProcedures,
            'procedures_variation' => $proceduresVariation,
        ]);
    }

    /**
     * Pacientes por remitente
     */
    public function patientsByReferrer()
    {
        $data = Patient::select(
                'referrer_name',
                DB::raw('COUNT(*) as total_patients')
            )
            ->whereNotNull('referrer_name')
            ->groupBy('referrer_name')
            ->orderByDesc('total_patients')
            ->get();

        return response()->json($data);
    }

    /**
     * Ingresos por remitente
     * (Procedure → MedicalEvaluation → Patient)
     */
    public function incomeByReferrer()
    {
        $data = DB::table('procedures')
            ->join('medical_evaluations', 'procedures.medical_evaluation_id', '=', 'medical_evaluations.id')
            ->join('patients', 'medical_evaluations.patient_id', '=', 'patients.id')
            ->select(
                'patients.referrer_name',
                DB::raw('SUM(procedures.total_amount) as total_income')
            )
            ->whereNotNull('patients.referrer_name')
            ->groupBy('patients.referrer_name')
            ->orderByDesc('total_income')
            ->get();

        return response()->json($data);
    }

    /**
     * Ingresos mensuales
     */
    public function incomeMonthly()
    {
        $data = Procedure::select(
                DB::raw('YEAR(procedure_date) as year'),
                DB::raw('MONTH(procedure_date) as month'),
                DB::raw('SUM(total_amount) as total_income')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return response()->json($data);
    }

    /**
     * Ingresos semanales (por día)
     */
    public function incomeWeekly()
    {
        $start = Carbon::now()->startOfWeek();
        $end   = Carbon::now()->endOfWeek();

        $data = Procedure::whereBetween('procedure_date', [$start, $end])
            ->select(
                DB::raw('DATE(procedure_date) as date'),
                DB::raw('SUM(total_amount) as total_income')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /**
     * Procedimientos más realizados
     */
    public function mostRequestedProcedures()
    {
        $data = ProcedureItem::select(
                'item_name',
                DB::raw('COUNT(*) as total_times'),
                DB::raw('SUM(price) as total_income')
            )
            ->groupBy('item_name')
            ->orderByDesc('total_times')
            ->limit(5)
            ->get();

        return response()->json($data);
    }

    /**
     * Ingresos por tipo de procedimiento
     */
    public function incomeByProcedureType()
    {
        $data = ProcedureItem::select(
                'item_name',
                DB::raw('SUM(price) as total_income')
            )
            ->groupBy('item_name')
            ->orderByDesc('total_income')
            ->get();

        return response()->json($data);
    }
}
