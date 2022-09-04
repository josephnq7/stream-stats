function page(path) {
    return () => import(/* webpackChunkName: '' */ `~/pages/${path}`).then(m => m.default || m)
}

export default [
    {path: '/', name: 'welcome', component: page('welcome.vue')},

    {path: '/login', name: 'login', component: page('auth/login.vue')},

    {
        path: '/stats/count-stream-by-game',
        name: 'stats.count-stream-by-game',
        component: page('stats/count_stream_by_game.vue')
    },
    {
        path: '/stats/top-views-by-game',
        name: 'stats.top-views-by-game',
        component: page('stats/top_views_by_game.vue')
    },
    {
        path: '/stats/top-streams',
        name: 'stats.top-streams',
        component: page('stats/top_streams.vue')
    },

    {
        path: '/stats/streams-by-hour',
        name: 'stats.streams-by-hour',
        component: page('stats/streams_by_hour.vue')
    },

    {path: '/home', name: 'home', component: page('home.vue')},

    {path: '*', component: page('errors/404.vue')}
]
