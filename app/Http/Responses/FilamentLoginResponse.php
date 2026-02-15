<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class FilamentLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = $request->user();

        if ($user?->hasRole('admin')) {
            return redirect()->to(Filament::getPanel('admin')->getUrl());
        }

        if ($user?->hasRole('foreman')) {
            return redirect()->to(Filament::getPanel('foreman')->getUrl());
        }

        if ($user?->hasRole('staff')) {
            return redirect()->to(Filament::getPanel('staff')->getUrl());
        }

        if ($user?->hasRole('management')) {
            return redirect()->to(Filament::getPanel('management')->getUrl());
        }

        return redirect()->to(url('/login'));
    }
}
