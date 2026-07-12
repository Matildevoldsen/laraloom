<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'legal.accepted'])->group(function (): void {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
});

Route::middleware(['auth', 'legal.accepted'])->group(function (): void {
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');
});
