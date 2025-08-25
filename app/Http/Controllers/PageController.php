<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function tentangKami()
    {
        return view('user.tentang.tentangkami');
    }
}
