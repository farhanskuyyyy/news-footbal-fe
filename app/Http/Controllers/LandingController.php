<?php

namespace App\Http\Controllers;

use App\Services\FootballPortalService;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(FootballPortalService $football): View
    {
        return view('landing.index', ['leagues' => $football->getLeagues(true) ?? []]);
    }
}
