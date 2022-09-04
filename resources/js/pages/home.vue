<template>
    <div class="row">
        <div class="col-lg-10 m-auto">
            <card :titleColorInfo="$t('stats_info')">
                <div class="row">
                    <div class="col-lg-4">
                        <card :titleColorPrimary="$t('share_tag')">
                            <div v-if="sharedTags.length">
                                <span v-for="(item, index) in sharedTags" :key="`${item.name}-${index}`"
                                      class="badge rounded-pill bg-info text-dark ms-2 text-wrap mt-2">{{ item.name }}</span>
                            </div>
                            <div v-else>
                                <div class="alert alert-warning" role="alert">
                                    No shared tags!
                                </div>
                            </div>
                        </card>
                    </div>
                    <div class="col-lg-4">
                        <card :titleColorPrimary="$t('share_stream')">
                            <div v-if="sharedStreams.length">
                                <span v-for="(item, index) in sharedStreams" :key="`${item.title}-${index}`"
                                      class="badge bg-info text-dark ms-2 text-wrap mt-2" >{{ item.title }}</span>
                            </div>
                            <div v-else>
                                <div class="alert alert-warning" role="alert">
                                    No Stream!
                                </div>
                            </div>
                        </card>
                    </div>
                    <div class="col-lg-4">
                        <card :titleColorPrimary="$t('viewer_count_need')">
                            <div v-if="viewerCountNeededToBeTop1000 > 0">
                                <span class="badge bg-info text-dark ms-2 text-wrap mt-2">{{ `[${nameOfLowestStreamFollowing}] : `}} <span class="fs-2 text-warning"> {{ viewerCountNeededToBeTop1000 }} </span></span>
                            </div>
                            <div v-else>
                                <div class="alert alert-warning" role="alert">
                                    All your following streams are in top 1000
                                </div>
                            </div>
                        </card>
                    </div>
                </div>

            </card>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    middleware: 'auth',

    metaInfo() {
        return {title: this.$t('home')}
    },
    data() {
        return {
            sharedTags: [],
            sharedStreams: [],
            viewerCountNeededToBeTop1000: 0,
            nameOfLowestStreamFollowing: '',
        };
    },
    mounted() {
        // debugger;
        axios.get("/api/stats/general-info").then(response => {
            let data = response.data.data;
            this.sharedTags = data.sharedTags;
            this.sharedStreams = data.sharedStreams;
            this.viewerCountNeededToBeTop1000 = data.viewerCountNeededToBeTop1000;
            this.nameOfLowestStreamFollowing = data.nameOfLowestStreamFollowing
        });
    },
}
</script>
