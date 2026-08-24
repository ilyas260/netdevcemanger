<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StatisticsController extends Controller
{
    /**
     * Display toner levels dashboard.
     */
    public function toner(): View
    {
        return view('statistics.toner');
    }

    /**
     * Display connectivity and latency dashboard.
     */
    public function connectivity(): View
    {
        return view('statistics.connectivity');
    }
}
