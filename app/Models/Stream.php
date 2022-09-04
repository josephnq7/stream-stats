<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Stream
 *
 * @property int $id
 * @property string $title
 * @property int|null $channel_id
 * @property int|null $game_id
 * @property int $viewer_count
 * @property string $start_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Channel|null $channel
 * @property-read \App\Models\Game|null $game
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Stream newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Stream newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Stream query()
 * @method static \Illuminate\Database\Eloquent\Builder|Stream whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stream whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stream whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stream whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stream whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stream whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stream whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stream whereViewerCount($value)
 * @mixin \Eloquent
 */
class Stream extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = [
        'title',
        'channel_id',
        'game_id',
        'viewer_count',
        'start_at',
        'id'
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'streams_tags');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
