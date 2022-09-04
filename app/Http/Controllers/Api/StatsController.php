<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Stream;
use App\Models\Tag;
use App\Models\User;
use App\Traits\CacheTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use romanzipp\Twitch\Twitch;

class StatsController extends Controller
{
    use CacheTrait;
    /**
     * @return JsonResponse
     */
    public function totalStreamsByGame(): JsonResponse
    {
        try {
            if ($cacheData = $this->getCache($this->keyTotalStreamsByGame)) {
                $countByGame = $cacheData;
            } else {
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

                $this->setCache($this->keyTotalStreamsByGame, $countByGame);
            }

            return response()->json(['data' => $countByGame]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function topViewsByGame(): JsonResponse
    {
        try {
            if ($cacheData = $this->getCache($this->keyTopViewsByGame)) {
                $topByGame = $cacheData;
            } else {
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

                $this->setCache($this->keyTopViewsByGame, $topByGame);
            }

            return response()->json(['data' => $topByGame]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function topStreams(): JsonResponse
    {
        try {
            if ($cacheData = $this->getCache($this->keyTopStreams)) {
                $streams = $cacheData;
            } else {
                $streams = Stream::select(
                    ['streams.title', 'channels.name AS channel_name', 'games.name', 'streams.viewer_count']
                )
                    ->join('games', 'games.id', '=', 'streams.game_id')
                    ->join('channels', 'channels.id', '=', 'streams.channel_id')
                    ->limit(100)->orderBy('viewer_count', 'DESC')->get();

                $streams = $streams->toArray();
                $this->setCache($this->keyTopStreams, $streams);
            }
            return response()->json(['data' => $streams]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function streamsByHour(): JsonResponse
    {
        try {
            if ($cacheData = $this->getCache($this->keyStreamsByHour)) {
                $streams = $cacheData;
            } else {
                $streams = Stream::selectRaw(
                    "COUNT(id) as total_streams, DATE_FORMAT(DATE_ADD(start_at, INTERVAL 30 MINUTE),'%m/%d/%Y %H:00') as start_at_hour"
                )
                    ->groupByRaw(("DATE_FORMAT(DATE_ADD(start_at, INTERVAL 30 MINUTE),'%m/%d/%Y %H:00')"))
                    ->orderByRaw('COUNT(id) DESC')
                    ->get();

                $streams = $streams->toArray();
                $this->setCache($this->keyStreamsByHour, $streams);
            }
            return response()->json(['data' => $streams]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @param Twitch $twitch
     * @return JsonResponse
     */
    public function generalInfo(Request $request, Twitch $twitch) : JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            $data = $user->getFollowingInfo($twitch);

            list($followingTagIds, $followingStreamIds, $lowestUserViewerCount, $lowestStreamName) = $data;

            $top1000 = Stream::select(['id', 'viewer_count'])
                ->orderBy('viewer_count', 'DESC')
                ->limit(1000);

            $sharedTags = Tag::select('name')
                ->join('streams_tags', 'streams_tags.tag_id', '=', 'tags.id')
                ->joinSub($top1000, 'streams', function ($join) {
                    $join->on('streams_tags.stream_id', '=', 'streams.id');
                })
                ->whereIn('streams.id', $followingStreamIds)
                ->whereIn('tags.code', $followingTagIds)
                ->distinct()
                ->get()
                ->toArray();

            $sharedStreamsWithTop = Stream::select('streams.title')
                ->joinSub($top1000, 'top_streams', function ($join) {
                    $join->on('top_streams.id', '=', 'streams.id');
                })
                ->whereIn('streams.id', $followingStreamIds)
                ->get()
                ->toArray();

            /** @var Stream $lowestStreamOnTop1000 */
//            $lowestStreamOnTop1000 = Stream::select('viewer_count')
//                                ->orderBy('viewer_count', 'DESC')
//                                ->offset(1000)
//                                ->limit(1)
//                                ->first();
            $lowestStreamOnTop1000 = Stream::select(['top_streams.viewer_count', 'top_streams.id'])
                ->fromSub($top1000, 'top_streams')
                ->orderBy('top_streams.viewer_count', 'ASC')
                ->limit(1)
                ->first();

            if (!empty($lowestStreamOnTop1000)) {
                $lowestViewerCountOnTop1000 = $lowestStreamOnTop1000->viewer_count;
                $numOfViewerCountNeeded = ($lowestUserViewerCount > $lowestViewerCountOnTop1000) ? 0 : ($lowestViewerCountOnTop1000 - $lowestUserViewerCount + 1);
            } else {
                $numOfViewerCountNeeded = 0;
            }

            return response()->json(
                [
                    'data' => [
                        'sharedTags' => $sharedTags,
                        'sharedStreams' => $sharedStreamsWithTop,
                        'viewerCountNeededToBeTop1000' => $numOfViewerCountNeeded,
                        'nameOfLowestStreamFollowing' => $lowestStreamName
                    ]
                ]
            );

        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
