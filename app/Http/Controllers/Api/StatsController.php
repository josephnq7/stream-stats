<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Stream;
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
                    if ($record->viewer_count > $currentTop) {
                        $topByGame[$record->id]['top'] = $record->viewer_count;
                        $topByGame[$record->id]['title'] = $record->title;
                    }
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

    /**
     * @return JsonResponse
     */
    public function topStreams() : JsonResponse
    {
        try {
            $streams = Stream::select(['streams.title', 'channels.name AS channel_name', 'games.name', 'streams.viewer_count'])
                ->join('games', 'games.id', '=', 'streams.game_id')
                ->join('channels', 'channels.id', '=', 'streams.channel_id')
                ->limit(100)->orderBy('viewer_count', 'DESC')->get();

            $streams = $streams->toArray();
            return response()->json(['data' => $streams]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}
