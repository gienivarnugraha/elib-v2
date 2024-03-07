<?php

namespace App\Core\Http\Controllers;

use App\Http\Controllers\Controller;

class ApplicationController extends Controller
{
    public function __invoke()
    {
        return view('application');
    }
}
