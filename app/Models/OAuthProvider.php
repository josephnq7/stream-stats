<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OAuthProvider
 *
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $provider_user_id
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider query()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider whereProviderUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthProvider whereUserId($value)
 * @mixin \Eloquent
 */
class OAuthProvider extends Model
{
    const PROVIDER_TWITCH = 'twitch';

    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_providers';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'access_token', 'refresh_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
