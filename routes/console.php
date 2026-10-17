<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Inspire Command
|--------------------------------------------------------------------------
*/
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Auto Shuffle Teams — Setiap Awal Bulan
|--------------------------------------------------------------------------
|
| Berjalan tanggal 1 jam 00:01 setiap bulan.
| Otomatis di-skip untuk divisi yang belum aktif (shuffle_enabled = false).
|
*/
Schedule::command('teams:shuffle')
    ->monthlyOn(1, '00:01')
    ->description('Auto rotate teams setiap awal bulan')
    ->withoutOverlapping();
