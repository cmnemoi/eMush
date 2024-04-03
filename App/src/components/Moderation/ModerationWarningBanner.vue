<template>
    <div class="warning-banner-container" v-if="showBanner">
        <div class="warning-banner" v-for="(warning, index) in (showAll ? warnings : warnings.slice(0, 1))" :key="index">
            <h1 class="banner-title">{{ $t('moderation.sanction.warning') }}</h1>
            <p class="banner-content">
                <span>{{ $t('moderation.sanctionReason') }} :</span>
                <span>{{ $t('moderation.reason.'+ warning.reason) }}.</span>
                <br>
                <span>{{ warning.message }}</span>
            </p>
            <button v-if="index === 0" class="button-toggle-show-all" @click="toggleShowAll">
                {{ showAll ? $t('moderation.reduce') : $t('moderation.showAll') + ' (' + warnings.length + ')' }}
            </button>
        </div>
    </div>
    <div class="dummy_space" v-if="showBanner">
    </div>
</template>

<script>

import qs from "qs";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";

export default {
    name: 'ModerationWarningBanner',
    props: {
        userId: {
            type: String,
            default: null
        }
    },
    data() {
        return {
            showBanner: true,
            showAll: true,
            warnings: []
        };
    },
    computed: {
        bannerHeight() {
            return this.showAll ? 'auto' : '10%'; // Limite la hauteur à 10% si showAll est false
        }
    },
    methods: {
        toggleShowAll() {
            this.showAll = !this.showAll;
        },
        loadData() {
            if (this.userId === null) {
                return;
            }
            this.loading = true;

            const params = {
                header: {
                    'accept': 'application/ld+json'
                },
                params: {},
                paramsSerializer: qs.stringify
            };

            qs.stringify(params.params['order'] = { ['startDate']: 'DEC' });
            params.params['moderationAction'] = 'warning';

            params.params['startDate[before]'] = 'now';
            params.params['endDate[after]'] = 'now';

            params.params['user.userId'] = this.userId;

            ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'moderation_sanctions'), params)
                .then((result) => {
                    return result.data;
                })
                .then((remoteRowData) => {
                    this.warnings = remoteRowData['hydra:member'];
                    this.loading = false;
                });
        }
    },
    beforeMount() {
        this.loadData();

        if (this.warnings.length > 0) {
            this.showBanner = true;
        }
        console.log(this.warnings.length);
    }
};
</script>


<style scoped>
.warning-banner-container {
    position: fixed;
    bottom: 0;
    width: 100%;
    display: flex;
    flex-direction: column-reverse;
    align-items: flex-end;
    padding: 10px;
    box-sizing: border-box;
}

.dummy_space {
    position: sticky;
    bottom: 0;
    width: 100%;
    height: 50px;
    display: flex;
    flex-direction: column-reverse;
    align-items: flex-end;
    padding: 10px;
    box-sizing: border-box;
}

.warning-banner {
    background-color: #f05b76;
    color: black;
    padding: 10px;
    margin-bottom: 2px;
    border-radius: 5px;
    width: 100%;
}

.banner-title {
    margin: 0;
    font-size: 18px;
}

.banner-content {
    margin-top: 5px;
    margin-bottom: 5px;
}

.banner-content span {
    margin-right: 10px;
}
.button-toggle-show-all {
    color: #4d4d4d;
    margin-left: auto;
}
</style>
