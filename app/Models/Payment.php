<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = ['member_id', 'package_id', 'amount', 'start_date', 'end_date', 'payment_method', 'payment_status', 'note','user_id'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
