<template>
    <button v-if="twitchAuth" class="btn btn-dark ms-auto" type="button" @click="login">
        {{ $t('login_with') }}
        <fa :icon="['fab', 'twitch']"/>
    </button>
</template>

<script>
import Cookies from 'js-cookie';
export default {
    name: 'LoginTwitch',

    computed: {
        twitchAuth: () => window.config.twitchAuth
    },

    mounted() {
        window.addEventListener('message', this.onMessage, false)
    },

    beforeDestroy() {
        window.removeEventListener('message', this.onMessage)
    },

    methods: {
        async login() {
            const newWindow = openWindow('', this.$t('login'))

            const url = await this.$store.dispatch('auth/fetchOauthUrl', {
                provider: 'twitch'
            });

            newWindow.location.href = url
        },

        /**
         * @param {MessageEvent} e
         */
        onMessage(e) {
            if (e.origin !== window.origin || !e.data.token) {
                return
            }

            let minutes_to_expire = e.data.expire_at;
            let expire_at = (new Date(new Date() * 1 + (minutes_to_expire * 60 * 1000)));

            this.$store.dispatch('auth/saveToken', {
                token: e.data.token,
                expire_at:expire_at
            })

            const intendedUrl = Cookies.get('intended_url')

            if (intendedUrl) {
                Cookies.remove('intended_url')
                this.$router.push({path: intendedUrl})
            } else {
                this.$router.push({name: 'home'})
            }
        }
    }
}

/**
 * @param  {Object} options
 * @return {Window}
 */
function openWindow(url, title, options = {}) {
    if (typeof url === 'object') {
        options = url
        url = ''
    }

    options = {url, title, width: 600, height: 720, ...options}

    const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screen.left
    const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screen.top
    const width = window.innerWidth || document.documentElement.clientWidth || window.screen.width
    const height = window.innerHeight || document.documentElement.clientHeight || window.screen.height

    options.left = ((width / 2) - (options.width / 2)) + dualScreenLeft
    options.top = ((height / 2) - (options.height / 2)) + dualScreenTop

    const optionsStr = Object.keys(options).reduce((acc, key) => {
        acc.push(`${key}=${options[key]}`)
        return acc
    }, []).join(',')

    const newWindow = window.open(url, title, optionsStr)

    if (window.focus) {
        newWindow.focus()
    }

    return newWindow
}
</script>
