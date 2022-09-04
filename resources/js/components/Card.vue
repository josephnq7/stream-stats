<template>
    <div class="card">
        <div v-if="title" class="card-header">
            {{ title }}
        </div>
        <div v-if="titleColorInfo" class="card-header">
            <div class='alert alert-info fw-bold' role='alert'> {{ titleColorInfo }} </div>
        </div>
        <div v-if="titleColorPrimary" class="card-header">
            <div class='alert alert-primary fw-bold' role='alert'> {{ titleColorPrimary }} </div>
        </div>

        <div class="card-body">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Card',

    props: {
        title: {type: String, default: null},
        titleColorInfo: {type: String, default: null},
        titleColorPrimary: {type: String, default: null}
    }
}
</script>
