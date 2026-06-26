<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;

    protected $primaryKey = 'specialization_id';
    protected $table = 'specialization';

    protected $fillable = ['name'];

    // Define the reverse Many-to-Many Relationship
    public function doctors()
    {
        return $this->belongsToMany(
            Doctor::class, 
            'doctor_specialization', 
            'specialization_id', 
            'doctor_id'
        );
    }
}