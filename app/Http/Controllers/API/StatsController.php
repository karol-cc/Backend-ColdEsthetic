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
     * Pacientes - Ingresos por remitente (Mes actual y total histórico)
     */
    public function referrerStats()
    {
        $now = Carbon::now();

        // Pacientes por remitente (total histórico)
        $patientsTotal = Patient::select(
                'referrer_name',
                DB::raw('COUNT(*) as total_patients')
            )
            ->whereNotNull('referrer_name')
            ->groupBy('referrer_name')
            ->get();

        // Pacientes por remitente (mes actual)
        $patientsMonth = Patient::select(
                'referrer_name',
                DB::raw('COUNT(*) as month_patients')
            )
            ->whereNotNull('referrer_name')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->groupBy('referrer_name')
            ->get();

        // Ingresos por remitente (total histórico)
        $incomeTotal = DB::table('procedures')
            ->join('medical_evaluations', 'procedures.medical_evaluation_id', '=', 'medical_evaluations.id')
            ->join('patients', 'medical_evaluations.patient_id', '=', 'patients.id')
            ->select(
                'patients.referrer_name',
                DB::raw('SUM(procedures.total_amount) as total_income')
            )
            ->whereNotNull('patients.referrer_name')
            ->groupBy('patients.referrer_name')
            ->get();

        // Ingresos por remitente (mes actual)
        $incomeMonth = DB::table('procedures')
            ->join('medical_evaluations', 'procedures.medical_evaluation_id', '=', 'medical_evaluations.id')
            ->join('patients', 'medical_evaluations.patient_id', '=', 'patients.id')
            ->select(
                'patients.referrer_name',
                DB::raw('SUM(procedures.total_amount) as month_income')
            )
            ->whereNotNull('patients.referrer_name')
            ->whereMonth('procedure_date', $now->month)
            ->whereYear('procedure_date', $now->year)
            ->groupBy('patients.referrer_name')
            ->get();

        // Merge de resultados
        $merged = [];

        foreach ($patientsTotal as $p) {
            $merged[$p->referrer_name] = [
                'referrer_name'   => $p->referrer_name,
                'total_patients'  => $p->total_patients,
                'month_patients'  => 0,
                'total_income'    => 0,
                'month_income'    => 0,
            ];
        }

        foreach ($patientsMonth as $pm) {
            if (isset($merged[$pm->referrer_name])) {
                $merged[$pm->referrer_name]['month_patients'] = $pm->month_patients;
            } else {
                $merged[$pm->referrer_name] = [
                    'referrer_name'   => $pm->referrer_name,
                    'total_patients'  => 0,
                    'month_patients'  => $pm->month_patients,
                    'total_income'    => 0,
                    'month_income'    => 0,
                ];
            }
        }

        foreach ($incomeTotal as $i) {
            if (isset($merged[$i->referrer_name])) {
                $merged[$i->referrer_name]['total_income'] = $i->total_income;
            } else {
                $merged[$i->referrer_name] = [
                    'referrer_name'   => $i->referrer_name,
                    'total_patients'  => 0,
                    'month_patients'  => 0,
                    'total_income'    => $i->total_income,
                    'month_income'    => 0,
                ];
            }
        }

        foreach ($incomeMonth as $im) {
            if (isset($merged[$im->referrer_name])) {
                $merged[$im->referrer_name]['month_income'] = $im->month_income;
            } else {
                $merged[$im->referrer_name] = [
                    'referrer_name'   => $im->referrer_name,
                    'total_patients'  => 0,
                    'month_patients'  => 0,
                    'total_income'    => 0,
                    'month_income'    => $im->month_income,
                ];
            }
        }

        return response()->json(array_values($merged));
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
     * Top 5 procedimientos por CANTIDAD (Demanda) del mes actual
     */
    public function topByDemand()
    {
        $data = ProcedureItem::select(
                'item_name',
                DB::raw('COUNT(*) as total_count')
            )
            // Filtro para el mes y año en curso
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('item_name')
            ->orderByDesc('total_count')
            ->limit(5)
            ->get();

        return response()->json($data);
    }

    /**
     * Top 5 procedimientos por INGRESOS (Valor) del mes actual
     */
    public function topByIncome()
    {
        $data = ProcedureItem::select(
                'item_name',
                DB::raw('SUM(price) as total_revenue')
            )
            // Filtro para el mes y año en curso
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('item_name')
            ->orderByDesc('total_revenue')
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
