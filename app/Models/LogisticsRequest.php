<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticsRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_name',
        'sender_address',
        'recipient_name',
        'recipient_address',
        'sender_state',
        'sender_city',
        'recipient_state',
        'recipient_city',
        'pickup_method',
        'delivery_method',
        'pickup_center_id',
        'dropoff_center_id',
        'sender_lat',
        'sender_lng',
        'recipient_lat',
        'recipient_lng',
        'distance_km',
        'weight',
        'package_length_cm',
        'package_width_cm',
        'package_height_cm',
        'description',
        'delivery_type',
        'amount',
        'tracking_id',
        'status',
        'waybill_path',
        'assigned_manager_id',
        'assigned_officer_id',
        'assigned_delivery_agent_id',
        'scheduled_pickup_at',
        'route_code',
        'last_status_updated_at',
        'agent_assignment_status',
        'agent_assignment_responded_at',
        'agent_fee_amount',
        'agent_commission_amount',
        'agent_paid_at',
        'price_breakdown',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'amount' => 'decimal:2',
        'scheduled_pickup_at' => 'datetime',
        'last_status_updated_at' => 'datetime',
        'agent_assignment_responded_at' => 'datetime',
        'agent_fee_amount' => 'decimal:2',
        'agent_commission_amount' => 'decimal:2',
        'agent_paid_at' => 'datetime',
        'sender_lat' => 'decimal:7',
        'sender_lng' => 'decimal:7',
        'recipient_lat' => 'decimal:7',
        'recipient_lng' => 'decimal:7',
        'distance_km' => 'decimal:2',
        'package_length_cm' => 'decimal:2',
        'package_width_cm' => 'decimal:2',
        'package_height_cm' => 'decimal:2',
        'price_breakdown' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(LogisticsStaff::class, 'assigned_officer_id');
    }

    public function assignedManager()
    {
        return $this->belongsTo(LogisticsStaff::class, 'assigned_manager_id');
    }

    public function assignedDeliveryAgent()
    {
        return $this->belongsTo(DeliveryAgent::class, 'assigned_delivery_agent_id');
    }

    public function pickupCenter()
    {
        return $this->belongsTo(LogisticsCenter::class, 'pickup_center_id');
    }

    public function dropoffCenter()
    {
        return $this->belongsTo(LogisticsCenter::class, 'dropoff_center_id');
    }
}
