<template>
    <div class="row">
        <div class="col-lg-10 m-auto">
            <card :title="$t('count_streams_by_game')">
                <vuetable ref="vuetable"
                          :api-mode="false"
                          :fields="fields"
                          :per-page="perPage"
                          :data=this.data
                ></vuetable>
            </card>
        </div>
    </div>
</template>

<script>
import Vuetable from 'vuetable-2/dist/vuetable-2'
import axios from "axios";

export default {
    name: "count_stream_by_game",
    components: {
        Vuetable
    },
    data() {
        return {
            fields: [
                {
                    name: 'name',
                    title: 'Game',
                },
                {
                    name: 'count',
                    title: 'Total Number of Streams'
                },
            ],
            perPage: 20,
            data: []
        };
    },
    watch: {
        data(newVal, oldVal) {
            debugger;
            this.$refs.vuetable.refresh();
        }
    },
    mounted() {
        debugger;
        axios.get("/api/stats/total-streams-by-game").then(response => {
            this.data = response.data.data;
        });
    },
}
</script>
