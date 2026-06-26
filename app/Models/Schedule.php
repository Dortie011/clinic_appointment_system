<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $primaryKey = 'schedule_id';
    protected $table = 'schedules';

    protected $fillable = [
        'doctor_id',
        'availability_date',
        'start_time',
        'end_time',
        'availability_status'
    ];

    /**
     * Get the doctor that owns this shift record.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class, 'schedule_id', 'schedule_id');
    }
}