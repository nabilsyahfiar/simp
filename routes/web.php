<?php

use App\Models\ReportPhoto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', function (Request $request) {
    $user = Auth::user();

    if (! $user) {
        return redirect('/login');
    }

    if ($user->hasRole('admin')) {
        return redirect('/admin');
    }

    if ($user->hasRole('foreman')) {
        return redirect('/foreman');
    }

    if ($user->hasRole('staff')) {
        return redirect('/staff');
    }

    if ($user->hasRole('management')) {
        return redirect('/management');
    }

    // Prevent redirect loops for authenticated users without any panel role.
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
});

Route::get('/report-photos/{photo}', function (ReportPhoto $photo): Response {
    $user = Auth::user();

    abort_unless($user, 403);

    $allowedRoles = ['admin', 'staff', 'management'];

    if (! $user->hasAnyRole($allowedRoles)) {
        $isForemanOwner = $user->hasRole('foreman')
            && (int) $photo->report?->foreman_id === (int) $user->id
            && (int) $photo->report?->unit?->assigned_foreman_id === (int) $user->id;

        abort_unless($isForemanOwner, 403);
    }

    return \Illuminate\Support\Facades\Storage::disk('public')->response($photo->file_path);
})->name('report-photos.show');
