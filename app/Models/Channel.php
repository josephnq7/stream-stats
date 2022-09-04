<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Channel
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Channel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel query()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Stream[] $streams
 * @property-read int|null $streams_count
 */
class Channel extends Model
{
    public $incrementing = false;

    protected $fillable = ['id', 'name',];

    public function streams() : HasMany
    {
        return $this->hasMany(Stream::class, 'channel_id', 'id');
    }
}
