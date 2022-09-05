# Stream Stats Site

> This website is aimed at helping Twitch viewers get a quick look at how the channels they watch compare to the top 1000 live streams.

<p align="center">
<img src="https://i.imgur.com/CC3iDv1.png">
</p>

## Technical Stack

- PHP
- Laravel 8
- VueJS + VueRouter + Vuex
- MySQL
- Javascript
- SPA
- CSS
- Bootstrap 5 + Font Awesome 5
- Laravel Socialite for OAuth login to Twitch
- Laravel Sanctum to authenticate API requests

## Installation
- Edit `.env` and set your database connection details
- Add below configuration to your `.env`
```
#Twitch configuration
TWITCH_CLIENT_ID=
TWITCH_CLIENT_SECRET=
TWITCH_REDIRECT_URI=

#Token expired after 60 minutes
TOKEN_EXPIRE_AFTER = 60
```
- `php artisan migrate`
- `npm install`
- Cron to add/refresh Streams data from Twitch:
    - `php artisan refresh_stream:cron --nTop=2000`
    - You can pass the `nTop` option to specify how many streams you want to fetch from the API. Above example, I'm fetching 2000 streams.

## Usage

#### Development

```bash
npm run dev
```

#### Production

```bash
npm run build
```
