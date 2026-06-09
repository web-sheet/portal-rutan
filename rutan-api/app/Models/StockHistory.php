<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $fillable = [
        'item_id',
        'item_request_id',
        'item_name',
        'quantity',
        'type',
        'stock_before',
        'stock_after',
        'action_by',
    ];
}