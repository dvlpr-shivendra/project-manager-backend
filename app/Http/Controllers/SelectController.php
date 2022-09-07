<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SelectController extends Controller
{
    public function users(Request $request)
    {
        $request->validate([
            'name' => ['required']
        ]);
        
        return User::where('name', 'ILIKE', "$request->name%")
            ->take(5)
            ->get();
    }
}
