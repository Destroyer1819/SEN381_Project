<?php

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Schedule;

// Schedules the pruning to be checked each day at 2 AM.
Schedule::command('model:prune', [
    '--model' => [ServiceRequest::class],
])->dailyAt('02:00');
