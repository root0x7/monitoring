<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SystemMetrics;
class MetricsController extends Controller
{
    public function index(){
        $metrics = new SystemMetrics();
        return $metrics->getAllMetrics();
    }
}
