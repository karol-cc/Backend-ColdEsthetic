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
        return response()->json([
            'total_patients' => Patient::count(),
            'total_sessions' => Procedure::count(),
            'total_procedures' => ProcedureItem::count(),
            'total_income' => Procedure::sum('total_amount'),
            'this_month_income' => Procedure::whereMonth('procedure_date', now()->month)
                ->whereYear('procedure_date', now()->year)
                ->sum('total_amount'),
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
