<template>
    <div class="daedalus_detail">
        <h1 class="daedalus_detail_title">
            {{$t('admin.daedalus.createDaedalus')}}
        </h1>
        <div class="daedalus_detail_content">
            <label for="daedalus_name">{{ $t('admin.daedalus.name') }}</label>
            <input
                id="daedalus_name"
                ref="daedalus_name"
                v-model="name"
                type="text"
            >
            <ErrorList v-if="errors.name" :errors="errors.name"></ErrorList>
        </div>
        <div class="flex-grow-1">
            <label for="daedalus_config">{{ $t('admin.daedalus.config') }}</label>
            <select v-model="config">
                <option v-for="option in configs" :value="option.id" :key="option.id">
                    {{ option.name }}
                </option>
            </select>
            <ErrorList v-if="errors.gameConfig" :errors="errors.gameConfig"></ErrorList>
        </div>
        <div class="flex-grow-2">
            <label for="daedalus_language">{{ $t('admin.daedalus.language') }}</label>
            <select v-model="language">
                <option v-for="option in languages" :value="option.language" :key="option.id">
                    {{ option.name }}
                </option>
            </select>
            <ErrorList v-if="errors.language" :errors="errors.language"></ErrorList>
        </div>
        <button class="action-button" type="submit" @click="save">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import DaedalusService from "@/services/daedalus.service";
import ErrorList from "@/components/Utils/ErrorList.vue";
import { handleErrors } from "@/utils/apiValidationErrors";
import store from "@/store";
import { GameConfig } from "@/entities/Config/GameConfig";
import qs from "qs";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { LocalizationConfig } from "@/entities/Config/LocalizationConfig";

export default defineComponent({
    name: "DaedalusDetailPage",
    components: {
        ErrorList
    },
    data() {
        return {
            configs: [GameConfig],
            languages: [LocalizationConfig],
            name: '',
            config: '',
            language: '',
            errors: {},
            loading: false,
        };
    },
    methods: {
        loadData() {
            this.loading = true;
            const params: any = {
                header: {
                    'accept': 'application/ld+json'
                },
                params: {},
                paramsSerializer: qs.stringify
            };

            ApiService.get(urlJoin(import.meta.env.VITE_API_URL+ 'game_configs'))
                .then((result) => {
                    return result.data;
                })
                .then((remoteRowData: any) => {
                    this.configs = remoteRowData['hydra:member'];
                });
            ApiService.get(urlJoin(import.meta.env.VITE_API_URL+ 'localization_configs'))
                .then((result) => {
                    return result.data;
                })
                .then((remoteRowData: any) => {
                    this.languages = remoteRowData['hydra:member'];
                    this.loading = false;
                });
        },
        save(): void {
            store.dispatch('gameConfig/setLoading', { loading: true });
            DaedalusService.createDaedalus(this.name, this.config, this.language)
                .then(() => (this.$router.push({ name: 'AdminDaedalusList' })))
                .catch((error) => {
                    if (error.response) {
                        if (error.response.data.violations) {
                            this.errors = handleErrors(error.response.data.violations);
                        }
                    } else if (error.request) {
                        // The request was made but no response was received
                        console.error(error.request);
                    } else {
                        // Something happened in setting up the request that triggered an Error
                        console.error('Error', error.message);
                    }
                })
                .finally(() => store.dispatch('gameConfig/setLoading', { loading: false }))
            ;
        }
    },
    beforeMount() {
        this.loadData();
    }
});
</script>

<style lang="scss" scoped>

.daedalus_detail {
    width: 35%;
    min-width: 338px;
}

.daedalus_detail_content {
    margin-bottom: 1.6em;
}

button {
    @include button-style();
    padding: 2px 15px 4px;
}

</style>
