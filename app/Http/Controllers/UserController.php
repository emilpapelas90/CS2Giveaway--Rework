<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Giveaway;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard() {
      return Inertia::render('user/Dashboard');
    }

    public function test() {
      return Inertia::render('user/test');
    }

    public function welcome() {
      return Inertia::render('user/Welcome');
    }
}
