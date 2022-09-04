<template>
    <div class="row">
        <div class="col-lg-7 m-auto">
            <card :title="$t('login')">
                <div class="mb-3 row">
                    <div class="col-md-7">
                        <!-- Twitch Login Button -->
                        <login-twitch/>
                    </div>
                </div>
            </card>
        </div>
    </div>
</template>

<script>
import LoginTwitch from "../../components/LoginTwitch";
export default {
    components: {LoginTwitch},
    middleware: 'guest'
}
</script>
