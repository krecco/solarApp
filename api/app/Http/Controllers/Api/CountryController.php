<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    /**
     * Get list of countries with ISO codes
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $countries = config('countries.countries');

        return response()->json([
            'data' => $countries
        ]);
    }
}
