<?php

namespace App\Traits;

use Cache;

trait CacheTrait
{
    protected $duration = '30';  //minutes

    public $keyTotalStreamsByGame = 'total_streams_by_game';
    public $keyTopViewsByGame = 'top_views_by_game';
    public $keyTopStreams = 'top_streams';
    public $keyStreamsByHour = 'streams_by_hour';
    /**
     * @param $key
     * @return string
     */
    public function generateKey($key) : string
    {
        return "__" . md5($key) . "__";
    }

    public function setCache($key, $value)
    {
        $key = $this->generateKey($key);
        Cache::put($key, $value, now()->addMinutes($this->duration));
    }

    public function getCache($key)
    {
        $key = $this->generateKey($key);
        return Cache::get($key);
    }
}
