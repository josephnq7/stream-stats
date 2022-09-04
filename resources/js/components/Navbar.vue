<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <router-link :to="{ name: user ? 'home' : 'welcome' }" class="navbar-brand">
                {{ appName }}
            </router-link>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar">
                <span class="navbar-toggler-icon"/>
            </button>

            <div id="navbar" class="collapse navbar-collapse">

                <ul class="navbar-nav ms-auto">
                    <!-- Authenticated -->
                    <template v-if="user">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark"
                               href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false"
                            >
                                Stats
                            </a>
                            <div class="dropdown-menu">
                                <router-link :to="{ name: 'stats.count-stream-by-game' }" class="dropdown-item ps-3">
                                    <fa icon="calculator" fixed-width/>
                                    {{ $t('count_streams_by_game') }}
                                </router-link>
                                <div class="dropdown-divider"/>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark"
                               href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false"
                            >
                                <img :src="user.avatar" class="rounded-circle profile-photo me-1">
                                {{ user.user_name }}
                            </a>
                            <div class="dropdown-menu">
                                <a href="#" class="dropdown-item ps-3" @click.prevent="logout">
                                    <fa icon="sign-out-alt" fixed-width/>
                                    {{ $t('logout') }}
                                </a>
                            </div>
                        </li>
                    </template>
                    <!-- Guest -->
                    <template v-else>
                        <li class="nav-item">
                            <router-link :to="{ name: 'login' }" class="nav-link" active-class="active">
                                {{ $t('login') }}
                            </router-link>
                        </li>
                    </template>
                </ul>

            </div>
        </div>
    </nav>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
    components: {},

    data: () => ({
        appName: window.config.appName
    }),

    computed: mapGetters({
        user: 'auth/user'
    }),

    methods: {
        async logout() {
            // Log out the user.
            await this.$store.dispatch('auth/logout')

            // Redirect to login.
            this.$router.push({name: 'login'})
        }
    }
}
</script>

<style scoped>
.profile-photo {
    width: 2rem;
    height: 2rem;
    margin: -.375rem 0;
}

.container {
    max-width: 1100px;
}
</style>
