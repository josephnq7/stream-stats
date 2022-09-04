<?php

namespace App\Models;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use romanzipp\Twitch\Twitch;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $user_name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|OAuthProvider[] $oauthProviders
 * @property-read int|null $oauth_providers_count
 * @property-read Collection|PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserName($value)
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['avatar'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the oauth providers.
     *
     * @return HasMany
     */
    public function oauthProviders(): HasMany
    {
        return $this->hasMany(OAuthProvider::class, 'user_id', 'id');
    }


    /**
     * dynamically get avatar picture for user based on provider that he/she used to login to the system
     *
     * @return string
     */
    public function getAvatarAttribute(): string
    {
        $tokenName = $this->tokens()->orderBy('id', 'DESC')->first()->value('name');
        $parts = explode('_', $tokenName);
        $provider = $parts[0] ?? '';

        if (!empty($provider)) {
            /** @var OAuthProvider $provider */
            $provider = $this->oauthProviders()->where('provider', $provider)->orderBy('id', 'DESC')->first();
            if (!empty($provider)) {
                return $provider->avatar;
            }
        }

        return '';
    }

    public function getTwitchUser()
    {
        return $this->oauthProviders()
            ->select(['access_token', 'provider_user_id'])
            ->where('provider', '=', OAuthProvider::PROVIDER_TWITCH)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * @param Twitch $twitch
     * @return array
     * @throws Exception
     */
    public function getFollowingInfo(Twitch $twitch) : array
    {
        /** @var OAuthProvider $twitchUser */
        $twitchUser = $this->getTwitchUser();

        if (empty($twitchUser)) {
            throw new Exception("Unable to get provider user!");
        }

        $twitchApiToken = $twitchUser->access_token;
        $providerUserId = $twitchUser->provider_user_id;
        $twitch->setToken($twitchApiToken);

        $followingTagIds = [];
        $followingStreamIds = [];
        $lowestViewCount = PHP_INT_MAX;
        $lowestStreamName = null;

        do {
            $nextCursor = null;

            // If this is not the first iteration, get the page cursor to the next set of results
            if (isset($result)) {
                $nextCursor = $result->next();
            }

            // Query the API with an optional cursor to the next results page
            $result = $twitch->getFollowedStreams(['user_id' => $providerUserId], $nextCursor);
            if (!$result->success()) {
                throw new \Exception(print_r($result->data(), 1));
            }

            $followingStreams = (array)$result->data();
            if (is_array($followingStreams) && count($followingStreams)) {
                foreach ($followingStreams as $stream) {
                    $stream = (array) $stream;
                    if (isset($stream['id']) && !empty($stream['id'])) {
                        $streamId = (int) $stream['id'];
                        $streamTitle = $stream['title'] ?? '';

                        $followingStreamIds[] = $streamId;

                        $viewerCount = isset($stream['viewer_count']) ? (int) $stream['viewer_count'] : 0;
                        if ($viewerCount < $lowestViewCount) {
                            $lowestViewCount = $viewerCount;
                            $lowestStreamName = $streamTitle;
                        }
                    }
                    $tagIds = isset($stream['tag_ids']) ? (array) $stream['tag_ids'] : [];
                    $followingTagIds = array_merge($followingTagIds, $tagIds);
                }
            }
            // Continue until there are no results left, or we reached to quantity that we want
        } while ($result->hasMoreResults());

        $followingTagIds = array_unique($followingTagIds);

        return [$followingTagIds, $followingStreamIds, $lowestViewCount, $lowestStreamName];
    }

}
