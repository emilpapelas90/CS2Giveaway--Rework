<?php

namespace App\Http\Controllers;

use App\Models\Items;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function fetch_items() {
      $api = env('STEAMWEB_API_KEY');
      $steam_id = env('STEAM_ID');

      //$response = file_get_contents("https://www.steamwebapi.com/steam/api/inventory?key={$api}&steam_id={$steam_id}&limit=5");
      $response = Http::get("https://www.steamwebapi.com/steam/api/inventory", [
        'key' => $api,
        'steam_id' => $steam_id,
        'limit' => 5
      ]);

      if ($response->failed()) {
        $message = $response->json('message') ?? 'Failed to fetch items.';
        return redirect()->route('admin.settings')->withErrors('error', $message);
      }

      $items = $response->json();

        foreach ($items as $item) {
            Items::updateOrCreate(
                ['asset_id' => $item['assetid']],
                [
                  'name' => $item['marketname'], //preg_replace('/\s*\([^)]*\)/', '', $item['marketname']),
                  'image' => $item['image'],
                  'price' => $item['priceavg7d'],
                  'color' => $item['color'],
                ]
            );
        }

      return redirect()->route('admin.settings')->with('success', 'Items Fetched Successfully.');
    }

    public function index() {
      return response()->json(Items::all());
    }
}
