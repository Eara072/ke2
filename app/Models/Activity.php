<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model {
    protected $fillable = [
        'user_id', 
        'description', // Kita pakai ini untuk gabungan text jika perlu
        'project_name',
        'activity_type',
        'start_time',
        'end_time',
        'achievement_type',
        'achievement_total',
        'attachment_path',
        'remarks'];
}