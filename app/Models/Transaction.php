<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'app',
        'konekita_order_id',
        'konekios_order_id',
        'konekoin_balance_id',
        'type',
        'durianpay_id',
        'access_token',
        'customer_id',
        'payment_id',
        'signature'
    ];
}
