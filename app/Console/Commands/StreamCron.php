<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\Game;
use App\Models\Stream;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Log;
use romanzipp\Twitch\Enums\GrantType;
use romanzipp\Twitch\Twitch;
use Throwable;

class StreamCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh_stream:cron {--nTop=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh top N of streams.';

    /** @var Twitch */
    protected $twitch;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Twitch $twitch)
    {
        parent::__construct();
        $this->twitch = $twitch;
        $login = $this->twitch->getOAuthToken(null, GrantType::CLIENT_CREDENTIALS, ['user:read:email']);

        if (!$login->success()) {
            print (print_r($login->data(), 1));
            exit();
        }
    }


    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            $data = $this->fetchData();
            if (is_array($data) && count($data)) {
                shuffle($data);

                print "\e[0;32;47m =========START TO INSERT TO DB ================ \e[0m\n\"";

                foreach ($data as $stream) {
                    $stream = (array)$stream;
                    $type = $stream['type'] ?? '';
                    if (strtolower($type) == 'live') {
                        //live-streaming
                        try {
                            DB::beginTransaction();

                            $channelName = $stream['user_name'] ?? null;
                            $channelId = $stream['user_id'] ?? null;
                            $gameId = $stream['game_id'] ?? null;
                            $gameName = $stream['game_name'] ?? null;
                            $tagCodes = $stream['tag_ids'] ?? [];
                            $streamId = $stream['id'] ?? null;
                            $streamTitle = $stream['title'] ?? null;
                            $viewerCount = $stream['viewer_count'] ?? 0;
                            $startedAt = $stream['started_at'] ?? null;

                            $startedAt = (!empty($startedAt)) ? (new \DateTime($startedAt))->format("Y-m-d H:i:s") : (new \DateTime())->format("Y-m-d H:i:s");

                            if (!empty($gameId) && !empty($gameName)) {
                                $game = Game::updateOrCreate(
                                    ['id' => $gameId],
                                    ['name' => $gameName]
                                );
                            }

                            if (!empty($channelId) && !empty($channelName)) {
                                $channel = Channel::updateOrCreate(
                                    ['id' => $channelId],
                                    ['name' => $channelName]
                                );
                            }

                            $streamModel = null;
                            if (!empty($streamId) && !empty($streamTitle)) {
                                $streamModel = Stream::updateOrCreate(
                                    ['id' => $streamId],
                                    [
                                        'title' => $streamTitle,
                                        'channel_id' => $channelId ?: null,
                                        'game_id' => $gameId ?: null,
                                        'viewer_count' => $viewerCount,
                                        'start_at' => $startedAt,
                                    ]
                                );
                            }


                            if (!empty($tagCodes) && !empty($channelId) && !empty($streamModel)) {
                                $tagCodes = (array) $tagCodes;
                                $countDbTags = Tag::whereIn('code', $tagCodes)->count();

                                if (count($tagCodes) != $countDbTags) {
                                    //tagCodes don't exist in DB yet
                                    $resultTags = $this->twitch->getStreamTags(['broadcaster_id' => $channelId]);
                                    $tags = (array)$resultTags->data();

                                    if (!empty($tags)) {
                                        foreach ($tags as $tag) {
                                            $tag = (array)$tag;
                                            $tagCode = $tag['tag_id'] ?? '';

                                            $names = $tag['localization_names'] ?? [];
                                            $names = (array)$names;
                                            $tagName = $names['en-us'] ?? '';

                                            if (!empty($tagCode) && !empty($tagName)) {
                                                $tag = Tag::updateOrCreate(
                                                    ['code' => $tagCode],
                                                    ['name' => $tagName]
                                                );

                                                $streamTagCount = DB::table('streams_tags')
                                                    ->where('tag_id', '=', $tag->id)
                                                    ->where('stream_id', '=', $streamModel->id)
                                                    ->count();

                                                if (empty($streamTagCount)) {
                                                    DB::table('streams_tags')
                                                        ->insert([
                                                                     'tag_id' => $tag->id,
                                                                     'stream_id' => $streamModel->id,
                                                                 ]);
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    //tagCodes already existed in DB
                                    foreach ($tagCodes as $tagCode) {
                                        $tag = Tag::whereCode($tagCode)->first();
                                        if (!empty($tag)) {
                                            $streamTagCount = DB::table('streams_tags')
                                                ->where('tag_id', '=', $tag->id)
                                                ->where('stream_id', '=', $streamModel->id)
                                                ->count();

                                            if (empty($streamTagCount)) {
                                                DB::table('streams_tags')
                                                    ->insert([
                                                                 'tag_id' => $tag->id,
                                                                 'stream_id' => $streamModel->id,
                                                             ]);
                                            }
                                        }
                                    }
                                }
                            }
                            DB::commit();
                        } catch (Throwable $e) {
                            print "\e[0;31;42m {$e->getMessage()} \e[0m\n\"";
                            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                            DB::rollBack();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            print "\e[0;31;42m {$e->getMessage()} \e[0m\n\"";
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        print "\e[0;32;47m =========DONE INSERTING TO DB ================ \e[0m\n\"";
    }

    protected function fetchData() : array
    {
        $nTop = $this->option('nTop');
        print "\e[0;32;47m =========START TO FETCH {$nTop} FROM TWITCH API ================ \e[0m\n\"";

        $data = [];

        try {
            do {
                $nextCursor = null;

                // If this is not the first iteration, get the page cursor to the next set of results
                if (isset($result)) {
                    $nextCursor = $result->next();
                }

                //sometimes we only need 4 items to reach $nTop, so just want to fetch 4
                $number = min(100, ($nTop - count($data)));

                // Query the API with an optional cursor to the next results page
                $result = $this->twitch->getStreams(['first' => $number], $nextCursor);
                if (!$result->success()) {
                    throw new \Exception(print_r($result->data(), 1));
                }

                $streams = (array)$result->data();
                if (is_array($streams) && count($streams)) {
                    $data = array_merge($data, $streams);
                }
                // Continue until there are no results left, or we reached to quantity that we want
            } while ($result->hasMoreResults() && count($data) < $nTop);
        } catch (\Exception $e) {
            print "\e[0;31;42m {$e->getMessage()} \e[0m\n\"";
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $data = [];
        }

        return $data;
    }
}
