<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Charts\CpuBusy;

class DashboardsController extends Controller
{
    public function system(CpuBusy $cpubusy){
        return view('dashboards.system',['cpubusy'=>$cpubusy]);
    }
}
