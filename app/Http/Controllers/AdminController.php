<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller {

    public function getDashboardStats(Request $request) {
        // 1. Ambil Parameter Tanggal (Default: Hari Ini)
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        // 2. QUERY AKTIVITAS BERDASARKAN RENTANG TANGGAL
        // Menggunakan whereBetween untuk mengambil data di antara 2 tanggal
        $activities = Activity::join('users', 'activities.user_id', '=', 'users.id')
            ->select('activities.*', 'users.name as employee_name')
            ->whereDate('activities.start_time', '>=', $startDate)
            ->whereDate('activities.start_time', '<=', $endDate)
            ->orderBy('activities.start_time', 'desc')
            ->get();

        $detailData = [];
        $summaryByType = []; 

        foreach ($activities as $act) {
            // Hitung Durasi
            $start = Carbon::parse($act->start_time);
            $end = Carbon::parse($act->end_time);
            $durationMinutes = $end->diffInMinutes($start);
            
            $hours = floor($durationMinutes / 60);
            $mins = $durationMinutes % 60;
            $durationString = "{$hours}j {$mins}m";

            // Masukkan ke Detail Table
            $detailData[] = [
                'id' => $act->id,
                'employee' => $act->employee_name,
                'project' => $act->project_name,
                'activity' => $act->activity_type,
                'date' => $start->format('d M Y'), // Tampilkan tanggal juga
                'start' => $start->format('H:i'),
                'end' => $end->format('H:i'),
                'duration_str' => $durationString,
                'remarks' => $act->remarks
            ];

            // Rekap Summary
            if (!isset($summaryByType[$act->activity_type])) {
                $summaryByType[$act->activity_type] = 0;
            }
            $summaryByType[$act->activity_type] += $durationMinutes;
        }

        // Format Summary
        $finalSummary = [];
        foreach ($summaryByType as $type => $totalMinutes) {
            $h = floor($totalMinutes / 60);
            $m = $totalMinutes % 60;
            $finalSummary[] = [
                'type' => $type,
                'total_time' => "{$h} Jam {$m} Menit"
            ];
        }

        // Format Label Tanggal untuk Header
        $dateLabel = ($startDate === $endDate) 
            ? Carbon::parse($startDate)->format('d M Y') 
            : Carbon::parse($startDate)->format('d M') . " - " . Carbon::parse($endDate)->format('d M Y');

        return response()->json([
            'date_label' => $dateLabel,
            'summary' => $finalSummary,
            'details' => $detailData
        ]);
    }
}