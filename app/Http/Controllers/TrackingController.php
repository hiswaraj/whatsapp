<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Handle incoming tracking requests.
     */
    public function index(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Tracking endpoint active.'
        ]);
    }
}
