<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    // Forces Eloquent to bind query structures to your custom primary key column string
    protected $primaryKey = 'patient_id';

    protected $table = 'patients';

    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'phone_num',
        'email',
        'address'
    ];
}