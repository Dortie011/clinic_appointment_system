<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
        'role',
        'email',
        'doctor_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the doctor profile associated with this user account.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }
}