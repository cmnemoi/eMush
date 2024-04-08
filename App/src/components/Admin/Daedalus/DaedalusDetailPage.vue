<template>
    <div class="daedalus_detail">
        <h2 class="daedalus_detail_title">
            {{$t('admin.daedalus.createDaedalus')}}
        </h2>
        <div class="daedalus_detail_content flex-row wrap">
            <Input
                :label="$t('admin.daedalus.name')"
                id="daedalus_name"
                v-model="name"
                type="text"
                :errors="errors.name"
            />
            <div class="select-default">
                <label for="daedalus_config">{{ $t('admin.daedalus.config') }}</label>
                <select id="daedalus_config" v-model="config">
                    <option v-for="option in configs" :value="option.id" :key="option.id">
                        {{ option.name }}
                    </option>
                </select>
                <ErrorList v-if="errors.gameConfig" :errors="errors.gameConfig"></ErrorList>
            </div>
            <div class="select-default">
                <label for="daedalus_language">{{ $t('admin.daedalus.language') }}</label>
                <select id="daedalus_language" v-model="language">
                    <option v-for="option in languages" :value="option.language" :key="option.id">
                        {{ option.name }}
                    </option>
                </select>
                <ErrorList v-if="errors.language" :errors="errors.language"></ErrorList>
            </div>
        </div>
        <UpdateConfigButtons :update="false" @create="save"/>
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
import Input from "@/components/Utils/Input.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";

export default defineComponent({
    name: "DaedalusDetailPage",
    components: {
        ErrorList,
        Input,
        UpdateConfigButtons
    },
    data() {
        return {
            configs: [GameConfig],
            languages: [LocalizationConfig],
            name: '',
            config: '',
            language: '',
            errors: {},
            loading: false
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

            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+ 'game_configs'))
                .then((result) => {
                    return result.data;
                })
                .then((remoteRowData: any) => {
                    this.configs = remoteRowData['hydra:member'];
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+ 'localization_configs'))
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

h2 { font-size: 1.6em; }

h3 { font-size: 1.3em; }

h2, h3 {
    margin: 2.8em 0 0.6em;

    &:first-child { margin-top: 0.6em; }
}

button {
    @include button-style();
    padding: 2px 15px 4px;
}

.select-default {
    width: 31%;
    min-width: 200px;
    align-self: flex-end;

    label {
        padding: 0 0.8em;
        transform: translateY(0.45em);
        word-break: break-word;
    }

    select {
        min-width: 5em;
        padding: 0.3em 0.6em;
        font-size: 1.3em;
        color: white;
        background: #222b6b;
        border: 1px solid transparentize(white, 0.8);
        border-radius: 1px;

        &:focus {
            outline: none;
            box-shadow: 0 0 0 3px transparentize(white, 0.85);
        }
    }
}

</style>
