<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Log;

class StatsController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function totalStreamsByGame() : JsonResponse
    {
        try {
            $countByGame = [];
            $games = Game::select(['games.id', 'games.name'])
                ->join('streams', 'games.id', '=', 'streams.game_id')
                ->cursor();

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

    /**
     * @return JsonResponse
     */
    public function topViewsByGame() : JsonResponse
    {
        try {
            $topByGame = [];
            $records = Game::select(['games.id', 'games.name', 'streams.viewer_count', 'streams.title'])
                ->join('streams', 'games.id', '=', 'streams.game_id')
                ->cursor();

            foreach ($records as $record) {
                if (!isset($topByGame[$record->id])) {
                    $topByGame[$record->id] = [
                        'name' => $record->name,
                        'top' => $record->viewer_count,
                        'title' => $record->title
                    ];
                } else {
                    $currentTop = $topByGame[$record->id]['top'];
                    $topByGame[$record->id]['top'] = ($record->viewer_count > $currentTop ? $record->viewer_count : $currentTop);
                }
            }

            usort($topByGame, function ($game1, $game2) {
                return $game2['top'] <=> $game1['top'];
            });

            return response()->json(['data' => $topByGame]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
