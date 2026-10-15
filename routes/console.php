<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tokens do app vencem em 30 dias (AuthTokenService). Remove os vencidos há mais de um dia.
Schedule::command('sanctum:prune-expired --hours=24')->daily();
