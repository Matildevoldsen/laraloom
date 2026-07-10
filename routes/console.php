<?php

use App\Jobs\DiscoverLaravelContent;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new DiscoverLaravelContent)
    ->hourly()
    ->onOneServer();
