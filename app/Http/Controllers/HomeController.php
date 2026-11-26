<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Send administrators to the dashboard and teachers to the interactive map.
     */
    public function index(): RedirectResponse
    {
        return Auth::user()->isAdmin()
            ? redirect()->route('users.index')
            : redirect()->route('welcome');
    }

    /**
     * Show the interactive map of Colombian regions.
     */
    public function show(): View
    {
        $region = Region::all();

        return view('welcome', compact('region'));
    }
}
