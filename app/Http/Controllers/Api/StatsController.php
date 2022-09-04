<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Log;

class StatsController extends Controller
{
    public function totalStreamsByGame()
    {
        try {
            $countByGame = [];
            $games = Game::select(['games.id', 'games.name'])
                ->join('streams', 'games.id', '=', 'streams.game_id')
                ->get();

            foreach ($games as $game) {
                /** @var Game $game */
                if (!isset($countByGame[$game->id])) {
                    $countByGame[$game->id] = [
                        'name' => $game->name,
                        'count' => 1
                    ];
                } else {
                    $countByGame[$game->id]['count'] += 1;
                }
            }

            usort($countByGame, function ($game1, $game2) {
                return $game2['count'] <=> $game1['count'];
            });

            return response()->json(['data' => $countByGame]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
