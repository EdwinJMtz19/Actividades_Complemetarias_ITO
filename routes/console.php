<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cierre automático de semestres vencidos (necesita el programador: `php artisan schedule:work`).
Schedule::command('semestres:cerrar-vencidos')->dailyAt('00:05');
