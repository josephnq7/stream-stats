<template>
    <div class="row">
        <div class="col-lg-10 m-auto">
            <card :title="$t('streams_by_hour')">
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
    name: "streams_by_hour",
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
                    name: 'total_streams',
                    title: 'Total Streams',
                    sortField: 'total_streams'
                },
                {
                    name: 'start_at_hour',
                    title: 'Start At Hour',
                    sortField: 'start_at_hour'
                },
            ],
            perPage: 15,
            data: []
        };
    },
    mounted() {
        // debugger;
        axios.get("/api/stats/streams-by-hour").then(response => {
            this.data = response.data.data;
        });
    },
}
</script>
