<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function show()
    {
        return view('kiosk.attendance');
    }
}