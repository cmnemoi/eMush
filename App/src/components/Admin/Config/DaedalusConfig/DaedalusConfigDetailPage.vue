<template>
    <div v-if="daedalusConfig" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.daedalusConfig.name')"
                id="daedalusConfig_name"
                v-model="daedalusConfig.name"
                type="text"
                :errors="errors.name"
            ></Input>
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.daedalusConfig.initOxygen')"
                id="daedalusConfig_initOxygen"
                v-model="daedalusConfig.initOxygen"
                type="number"
                :errors="errors.initOxygen"
            ></Input>
            <Input
                :label="$t('admin.daedalusConfig.initFuel')"
                id="daedalusConfig_initFuel"
                v-model="daedalusConfig.initFuel"
                type="number"
                :errors="errors.initFuel"
            ></Input>
            <Input
                :label="$t('admin.daedalusConfig.initHull')"
                id="daedalusConfig_initHull"
                v-model="daedalusConfig.initHull"
                type="number"
                :errors="errors.initHull"
            ></Input>
            <Input
                :label="$t('admin.daedalusConfig.initShield')"
                id="daedalusConfig_initShield"
                v-model="daedalusConfig.initShield"
                type="number"
                :errors="errors.initShield"
            ></Input>
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.daedalusConfig.dailySporeNb')"
                id="daedalusConfig_dailySporeNb"
                v-model="daedalusConfig.dailySporeNb"
                type="number"
                :errors="errors.dailySporeNb"
            ></Input>
            <Input
                :label="$t('admin.daedalusConfig.maxOxygen')"
                id="daedalusConfig_maxOxygen"
                v-model="daedalusConfig.maxOxygen"
                type="number"
                :errors="errors.maxOxygen"
            ></Input>
            <Input
                :label="$t('admin.daedalusConfig.maxFuel')"
                id="daedalusConfig_maxFuel"
                v-model="daedalusConfig.maxFuel"
                type="number"
                :errors="errors.maxFuel"
            ></Input>
            <Input
                :label="$t('admin.daedalusConfig.maxHull')"
                id="daedalusConfig_maxHull"
                v-model="daedalusConfig.maxHull"
                type="number"
                :errors="errors.maxHull"
            ></Input>
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.daedalusConfig.maxShield')"
                id="daedalusConfig_maxShield"
                v-model="daedalusConfig.maxShield"
                type="number"
                :errors="errors.maxShield"
            ></Input>
            <Input
                :label="$t('admin.daedalusConfig.nbMush')"
                id="daedalusConfig_nbMush"
                v-model="daedalusConfig.nbMush"
                type="number"
                :errors="errors.nbMush"
            ></Input>
            <Input
                :label="$t('admin.daedalusConfig.cyclePerGameDay')"
                id="daedalusConfig_cyclePerGameDay"
                v-model="daedalusConfig.cyclePerGameDay"
                type="number"
                :errors="errors.cyclePerGameDay"
            ></Input>
            <Input
                :label="$t('admin.daedalusConfig.cycleLength')"
                id="daedalusConfig_cycleLength"
                v-model="daedalusConfig.cycleLength"
                type="number"
                :errors="errors.cycleLength"
            ></Input>
        </div>
        <h3>{{ $t('admin.daedalusConfig.placeConfigs') }}</h3>
        <ChildCollectionManager :children="daedalusConfig.placeConfigs" @addId="selectNewPlaceConfigs" @remove="removePlaceConfig">
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { RandomItemPlaces } from "@/entities/Config/RandomItemPlaces";
import { PlaceConfig } from "@/entities/Config/PlaceConfig";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { removeItem } from "@/utils/misc";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";

interface DaedalusConfigState {
    daedalusConfig: null|DaedalusConfig
    errors: any
}

export default defineComponent({
    name: "DaedalusConfigDetailPage",
    components: {
        ChildCollectionManager,
        Input,
        UpdateConfigButtons,
    },
    data: function (): DaedalusConfigState {
        return {
            daedalusConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if (this.daedalusConfig === null) return;
            
            const newDaedalusConfig = this.daedalusConfig;
            newDaedalusConfig.id = null;
            if (this.daedalusConfig.randomItemPlaces){
                const newRandomItemPlaces = this.daedalusConfig.randomItemPlaces;
                newRandomItemPlaces.id = null;
                newRandomItemPlaces.name = newDaedalusConfig.name;
                GameConfigService.createRandomItemPlaces(newRandomItemPlaces)
                    .then((res: RandomItemPlaces | null) => {
                        newDaedalusConfig.randomItemPlaces = res;
                        GameConfigService.createDaedalusConfig(newDaedalusConfig)
                            .then((res: DaedalusConfig | null) => {
                                const newDaedalusConfigUrl = urlJoin(process.env.VUE_APP_URL + "/config/daedalus-config", String(res?.id));
                                window.location.href = newDaedalusConfigUrl;
                            });
                    });
            }
        },
        update(): void {
            if (this.daedalusConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateDaedalusConfig(this.daedalusConfig)
                .then((res: DaedalusConfig | null) => {
                    this.daedalusConfig = res;
                    if (this.daedalusConfig !== null) {
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'daedalus_configs', String(this.daedalusConfig.id), 'random_item_places'))
                            .then((result) => {                                
                                if (this.daedalusConfig instanceof DaedalusConfig) {
                                    this.daedalusConfig.randomItemPlaces = (new RandomItemPlaces()).load(result.data);
                                }
                            });
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'daedalus_configs', String(this.daedalusConfig.id), 'place_configs?pagination=false'))
                            .then((result) => {
                                const placeConfigs: PlaceConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentPlaceConfig = (new PlaceConfig()).load(datum);
                                    placeConfigs.push(currentPlaceConfig);
                                });
                                if (this.daedalusConfig instanceof DaedalusConfig) {
                                    this.daedalusConfig.placeConfigs = placeConfigs;
                                }
                            });
                    }
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
        selectNewPlaceConfigs(selectedId: number): void {
            GameConfigService.loadPlaceConfig(selectedId).then((res) => {
                if (res && this.daedalusConfig && this.daedalusConfig.placeConfigs) {
                    this.daedalusConfig.placeConfigs.push(res);
                }
            });
        },
        removePlaceConfig(child: any): void {
            if (this.daedalusConfig && this.daedalusConfig.placeConfigs) {
                this.daedalusConfig.placeConfigs = removeItem(this.daedalusConfig.placeConfigs, child);
            }
        }
    },
    beforeMount() {
        const daedalusConfigId = String(this.$route.params.daedalusConfigId);
        GameConfigService.loadDaedalusConfig(Number(daedalusConfigId)).then((res: DaedalusConfig | null) => {
            this.daedalusConfig = res;
            ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'daedalus_configs', daedalusConfigId, 'random_item_places'))
                .then((result) => {
                    if (this.daedalusConfig instanceof DaedalusConfig) {
                        this.daedalusConfig.randomItemPlaces = (new RandomItemPlaces()).load(result.data);
                    }
                });
            ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'daedalus_configs', daedalusConfigId, 'place_configs?pagination=false'))
                .then((result) => {
                    const placeConfigs : PlaceConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentPlaceConfig = (new PlaceConfig()).load(datum);
                        placeConfigs.push(currentPlaceConfig);
                    });
                    if (this.daedalusConfig instanceof DaedalusConfig) {
                        this.daedalusConfig.placeConfigs = placeConfigs;
                    }
                });
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
