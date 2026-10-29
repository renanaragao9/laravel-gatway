<?php

namespace App\Http\Controllers;

use App\Models\Ong;

class HomeController extends Controller
{
    public function index()
    {
        $ongs = Ong::query()
            ->where('active', true)
            ->orderBy('name')
            ->paginate(9);

        return view('home', ['ongs' => $ongs]);
    }
}
