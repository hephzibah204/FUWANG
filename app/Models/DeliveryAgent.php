<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAgent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'state',
        'city',
        'address',
        'phone_number',
        'means_of_identification',
        'identification_number',
        'proof_of_address',
        'next_of_kin_name',
        'next_of_kin_phone',
        'availability_status',
        'rating',
        'approval_status',
    ];

    /**
     * Get the user that owns the delivery agent profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}