<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Giveaway;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard() {
      return Inertia::render('admin/Dashboard');
    }

    public function giveaways() {
      $giveaways = Giveaway::all();
      return Inertia::render('admin/giveaway/View', ['giveaways' => $giveaways]);
    }

    public function create() {
      return Inertia::render('admin/giveaway/Create');
    }

    public function store(Request $request) {

      //dd($request->requirements);

      $serverSeed = Str::random(64);

      $validateData = $request->validate([
        'skin_name' => ['required', 'string', 'max:255'],
        'image' => ['required', 'string', 'max:2048'],
        'value' => ['required', 'numeric', 'min:0'],
        'rarity' => ['required', 'string', 'max:255'],
        'entries' => ['nullable', 'integer', 'min:0'],
        'max_entries' => ['required', 'integer', 'min:1'],
        'end_time' => ['required', 'date'],
        'is_active' => ['required', 'boolean'],
      ]);

      $validateData['end_time'] = Carbon::parse($request->end_time)->format('Y-m-d H:i:s');

      $validateData['server_seed'] = $serverSeed;
      $validateData['server_seed_hashed'] = hash('sha256', $serverSeed);
      $validateData['nonce'] = 0;

      Giveaway::create($validateData);
      return redirect()->route('admin.giveaways')->with('success', 'Giveaway added successfully!');

    }

    public function settings() {
      return Inertia::render('admin/Settings');
    }

}
