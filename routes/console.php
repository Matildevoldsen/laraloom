<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('curation:discover --sync')
    ->hourly()
    ->onOneServer();
