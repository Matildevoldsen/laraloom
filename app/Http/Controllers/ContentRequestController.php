<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContentRequest;
use App\Models\ContentRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContentRequestController extends Controller
{
    public function create(): View
    {
        return view('legal.content-request');
    }

    public function store(StoreContentRequest $request): RedirectResponse
    {
        ContentRequest::create($request->validated());

        return to_route('legal.content-request')->with(
            'status',
            'Your request has been recorded for review.',
        );
    }
}
