<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $primaryKey = 'doctor_id';
    protected $table = 'doctors'; // Make sure this matches your migration name (singular or plural)

    // UPDATE THIS ARRAY RIGHT HERE:
    protected $fillable = [
        'first_name',
        'last_name',
        'phone', 
        'email',
        'room_num'
    ];

    public function specializations()
    {
        return $this->belongsToMany(
            Specialization::class,          
            'doctor_specialization',        
            'doctor_id',                    
            'specialization_id'             
        );
    }
    /**
     * Get the user account associated with this doctor.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'doctor_id', 'doctor_id');
    }
}