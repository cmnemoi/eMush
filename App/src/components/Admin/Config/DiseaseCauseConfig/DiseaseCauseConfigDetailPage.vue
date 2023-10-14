<template>
    <div v-if="diseaseCauseConfig" class="center">
        <h2>{{ $t('admin.diseaseCauseConfig.pageTitle') }} {{ diseaseCauseConfig.causeName }}</h2>
        <div class="flex-row">
            <Input
                :label="$t('admin.diseaseCauseConfig.name')"
                id="diseaseCauseConfig_name"
                v-model="diseaseCauseConfig.name"
                type="text"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.diseaseCauseConfig.causeName')"
                id="diseaseCauseConfig_name"
                v-model="diseaseCauseConfig.causeName"
                type="text"
                :errors="errors.causeName"
            />
        </div>
        <MapManager
            :label="$t('admin.diseaseCauseConfig.diseases')"
            :map="diseaseCauseConfig.diseases"
            mapIndexesType="string"
            mapValuesType="number"
            @addTuple="addDisease"
            @removeIndex="removeDisease"
        />
        <UpdateConfigButtons
            @create="create"
            @update="update"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import { DiseaseCauseConfig } from "@/entities/Config/DiseaseCauseConfig";
import Input from "@/components/Utils/Input.vue";
import MapManager from "@/components/Utils/MapManager.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import urlJoin from "url-join";

interface DiseaseCauseConfigState {
    diseaseCauseConfig: null|DiseaseCauseConfig
    errors: any
}

export default defineComponent({
    name: "DiseaseCauseConfig",
    components: {
        Input,
        MapManager,
        UpdateConfigButtons
    },
    data: function (): DiseaseCauseConfigState {
        return {
            diseaseCauseConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if (this.diseaseCauseConfig === null) return;
        
            // @ts-ignore
            const newDiseaseCauseConfig = this.diseaseCauseConfig;
            newDiseaseCauseConfig.id = null;
            
            GameConfigService.createDiseaseCauseConfig(newDiseaseCauseConfig).then((res: DiseaseCauseConfig | null) => {
                const newDiseaseCauseConfigUrl = urlJoin(import.meta.env.VITE_URL + '/config/disease-cause-config', String(res?.id));
                window.location.href = newDiseaseCauseConfigUrl;
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
            if (this.diseaseCauseConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateDiseaseCauseConfig(this.diseaseCauseConfig)
                .then((res: DiseaseCauseConfig | null) => {
                    this.diseaseCauseConfig = res;
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
        addDisease(tuple: any): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.diseaseCauseConfig && this.diseaseCauseConfig.diseases) {
                this.diseaseCauseConfig.diseases.set(index, value);
            }
        },
        removeDisease(index: string): void {
            if (this.diseaseCauseConfig && this.diseaseCauseConfig.diseases) {
                this.diseaseCauseConfig.diseases.delete(index);
            }
        }
    },
    beforeMount() {
        const diseaseCauseConfigId = Number(this.$route.params.diseaseCauseConfigId);
        GameConfigService.loadDiseaseCauseConfig(diseaseCauseConfigId).then((res: DiseaseCauseConfig | null) => {
            this.diseaseCauseConfig = res;
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
