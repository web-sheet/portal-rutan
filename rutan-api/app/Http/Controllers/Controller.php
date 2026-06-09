<?php

namespace App\Http\Controllers;
use App\Models\StockHistory;
abstract class Controller
{
   

public function index()
{
    return StockHistory::latest()->get();
}
}
