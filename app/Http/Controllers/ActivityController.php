<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityController extends Controller {

    // 1. Ambil List User (Tidak berubah)
    public function getUsers() {
        try {
            $users = User::where('is_active', true)->get();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Simpan Laporan (DIPERBARUI)
    public function store(Request $request) {
        
        // A. Validasi yang lebih lengkap sesuai Form baru
        $this->validate($request, [
            'user_id'       => 'required|exists:users,id',
            'pin'           => 'required|string',
            
            // Kolom-kolom baru
            'project_name'  => 'required|string',
            'activity_type' => 'required|string',
            'start_working' => 'required', // Format: YYYY-MM-DD HH:mm
            'end_working'   => 'required', 
            'attachment'    => 'nullable|file|max:5120' // Maksimal 5MB
        ]);

        // B. Cek PIN Keamanan
        $user = User::find($request->user_id);
        if ((string)$user->pin !== (string)$request->pin) {
            return response()->json(['message' => '⛔ PIN Salah!'], 401);
        }

        // C. Handle Upload File (Jika ada lampiran)
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            // Nama file unik: time_namaasli.jpg
            $filename = time() . '_' . $file->getClientOriginalName();
            // Simpan ke folder public/uploads
            $file->move(base_path('public/uploads'), $filename);
            $attachmentPath = 'uploads/' . $filename;
        }

        // D. Simpan ke Database
        // Catatan: Kolom-kolom ini harus dibuat di database nanti (Langkah selanjutnya)
        $activity = Activity::create([
            'user_id'           => $request->user_id,
            'project_name'      => $request->project_name,
            'activity_type'     => $request->activity_type,
            'start_time'        => $request->start_working,
            'end_time'          => $request->end_working,
            'achievement_type'  => $request->achievement_type,  // Opsional
            'achievement_total' => $request->achievement_total, // Opsional
            'remarks'           => $request->remarks,           // Opsional
            'attachment_path'   => $attachmentPath,             // Path file foto/dokumen
            
            // Kita isi description otomatis gabungan dari project & activity
            // supaya aplikasi lama (jika ada) tidak error
            'description'       => $request->activity_type . " di " . $request->project_name 
        ]);

        // E. Update Timestamp User (Untuk CronJob Pengingat)
        $user->last_activity_at = Carbon::now();
        $user->save();

        return response()->json([
            'message' => '✅ Laporan Lengkap Berhasil Disimpan!',
            'data' => $activity
        ]);
    }
}