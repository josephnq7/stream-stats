<template>
    <div class="row">
        <div class="col-lg-7 m-auto">
            <card :title="$t('login')">
                <div class="mb-3 row">
                    <div class="col-md-7">
                    </div>
                </div>
            </card>
        </div>
    </div>
</template>

<script>
export default {
    middleware: 'guest'
}
</script>
