<?php

namespace App\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Chart;
// use ConsoleTVs\Charts\Classes\Apexcharts\Chart;


class CpuBusy extends Chart
{
    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setType('donut')
        ->setHeight(300)
        ->dataset('CPU Busy', 'donut', [67])
        ->backgroundColor('#facc15');

        $this->options([
            'plotOptions' => [
                'donut' => [
                    'dataLabels' => [
                        'name' => [
                            'fontSize' => '22px',
                        ],
                        'value' => [
                            'fontSize' => '16px',
                        ],
                        'total' => [
                            'show' => true,
                            'label' => 'Jami',
                            'formatter' => \Illuminate\Support\Js::from('function () { return "100%"; }'),
                        ],
                    ],
                ],
            ],
        ]);
    }
}
