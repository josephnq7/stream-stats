<template>
    <div class="row">
        <div class="col-lg-10 m-auto">
            <card :title="$t('count_streams_by_game')">
                <vuetable ref="vuetable"
                          :api-mode="false"
                          :fields="fields"
                          :per-page="perPage"
                          :css="css.table"
                          :show-sort-icons="true"
                          :data-manager="dataManager"
                          pagination-path="pagination"
                          @vuetable:pagination-data="onPaginationData"/>

                <div style="padding-top:10px">
                    <vuetable-pagination
                        ref="pagination"
                        :css="css.pagination"
                        @vuetable-pagination:change-page="onChangePage" />
                </div>
            </card>
        </div>
    </div>
</template>

<script>
import Vuetable from 'vuetable-2/dist/vuetable-2'
// import VueTablePaginationBootstrap4 from "../../components/VueTablePaginationBootstrap4";
import VuetablePagination from "vuetable-2/src/components/VuetablePagination";
import axios from "axios";
import _ from "lodash";
import CssConfig from "../../plugins/bootstrap4CssConfig.js";


export default {
    name: "count_stream_by_game",
    components: {
        Vuetable,
        VuetablePagination
        // VueTablePaginationBootstrap4
    },
    data() {
        return {
            css: CssConfig,
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
            // debugger;
            this.$refs.vuetable.refresh();
        }
    },
    mounted() {
        // debugger;
        axios.get("/api/stats/total-streams-by-game").then(response => {
            this.data = response.data.data;
        });
    },

    methods: {
        onPaginationData(paginationData) {
            this.$refs.pagination.setPaginationData(paginationData);
        },
        onChangePage(page) {
            this.$refs.vuetable.changePage(page);
        },
        dataManager(sortOrder, pagination) {
            if (this.data.length < 1) return;

            let local = this.data;

            // sortOrder can be empty, so we have to check for that as well
            if (sortOrder.length > 0) {
                console.log("orderBy:", sortOrder[0].sortField, sortOrder[0].direction);
                local = _.orderBy(
                    local,
                    sortOrder[0].sortField,
                    sortOrder[0].direction
                );
            }

            pagination = this.$refs.vuetable.makePagination(
                local.length,
                this.perPage
            );
            console.log('pagination:', pagination)
            let from = pagination.from - 1;
            let to = from + this.perPage;

            return {
                pagination: pagination,
                data: _.slice(local, from, to)
            };
        }
    }
}
</script>
