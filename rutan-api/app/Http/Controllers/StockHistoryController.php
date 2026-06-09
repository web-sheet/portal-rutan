<?php

namespace App\Http\Controllers;

use App\Models\StockHistory;

abstract class StockHistoryController
{


    public function index()
    {
        return StockHistory::latest()->get();
    }
}
