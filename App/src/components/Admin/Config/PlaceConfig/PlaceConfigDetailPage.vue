<template>
    <div v-if="placeConfig" class="center">
        <h2>{{ $t('admin.placeConfig.pageTitle') }} <em>{{ placeConfig.placeName }}</em></h2>
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.placeConfig.name')"
                id="placeConfig_name"
                v-model="placeConfig.name"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.placeConfig.placeName')"
                id="placeConfig_placeName"
                v-model="placeConfig.placeName"
                type="text"
                :errors="errors.placeName"
            ></Input>
            <Input
                :label="$t('admin.placeConfig.type')"
                id="placeConfig_type"
                v-model="placeConfig.type"
                type="text"
                :errors="errors.type"
            ></Input>
        </div>
        <h3>{{ $t('admin.placeConfig.equipments') }}</h3>
        <StringArrayManager
            :array="placeConfig.equipments"
            id="placeConfig_equipments"
            @add-element="placeConfig.equipments.push($event)"
            @remove-element="placeConfig?.equipments.splice(placeConfig?.equipments.indexOf($event), 1)"
        ></StringArrayManager>
        <h3>{{ $t('admin.placeConfig.items') }}</h3>
        <StringArrayManager
            :array="placeConfig.items"
            id="placeConfig_items"
            @add-element="placeConfig.items.push($event)"
            @remove-element="placeConfig.items.splice(placeConfig.items.indexOf($event), 1)"
        ></StringArrayManager>
        <h3>{{ $t('admin.placeConfig.doors') }}</h3>
        <StringArrayManager
            :array="placeConfig.doors"
            id="placeConfig_doors"
            @add-element="placeConfig.doors.push($event)"
            @remove-element="placeConfig.doors.splice(placeConfig.doors.indexOf($event), 1)"
        ></StringArrayManager>
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { PlaceConfig } from "@/entities/Config/PlaceConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import StringArrayManager from "@/components/Utils/StringArrayManager.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import urlJoin from "url-join";

interface PlaceConfigState {
    placeConfig: null|PlaceConfig
    errors: any
}

export default defineComponent({
    name: "PlaceConfigDetailPage",
    components: {
        Input,
        StringArrayManager,
        UpdateConfigButtons
    },
    data: function (): PlaceConfigState {
        return {
            placeConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if(this.placeConfig === null) return;

            const newPlaceConfig = this.placeConfig;
            newPlaceConfig.id = null;

            GameConfigService.createPlaceConfig(newPlaceConfig)
                .then((res: PlaceConfig | null) => {
                    const newPlaceConfigUrl = urlJoin(import.meta.env.VITE_APP_URL + '/config/place-config', String(res?.id));
                    window.location.href = newPlaceConfigUrl;
                })
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
                });
        },
        update(): void {
            if (this.placeConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updatePlaceConfig(this.placeConfig)
                .then((res: PlaceConfig | null) => {
                    this.placeConfig = res;
                })
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
                });
        }
    },
    beforeMount() {
        const placeConfigId = Number(this.$route.params.placeConfigId);
        GameConfigService.loadPlaceConfig(placeConfigId).then((res: PlaceConfig | null) => {
            this.placeConfig = res;
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
