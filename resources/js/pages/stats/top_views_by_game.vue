<template>
    <div class="row">
        <div class="col-lg-10 m-auto">
            <card :title="$t('top_views_by_game')">
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
                    <vue-table-pagination-bootstrap4
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
import VueTablePaginationBootstrap4 from "../../components/VueTablePaginationBootstrap4";
import axios from "axios";
import CssConfig from "../../plugins/bootstrap4CssConfig.js";
import {dataTableMixin} from "../../mixins/data_table";


export default {
    name: "top_views_by_game",
    middleware: 'auth',
    components: {
        Vuetable,
        VueTablePaginationBootstrap4
    },
    mixins: [dataTableMixin],
    data() {
        return {
            css: CssConfig,
            fields: [
                {
                    name: 'name',
                    title: 'Game',
                },
                {
                    name: 'top',
                    title: 'Top Viewer Count',
                    sortField: 'top'
                },
                {
                    name: 'title',
                    title: 'Stream Title',
                },
            ],
            perPage: 15,
            data: []
        };
    },
    mounted() {
        // debugger;
        axios.get("/api/stats/top-views-by-game").then(response => {
            this.data = response.data.data;
        });
    },
}
</script>
