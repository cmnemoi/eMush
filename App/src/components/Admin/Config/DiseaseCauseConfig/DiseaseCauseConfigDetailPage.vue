<template>
    <div v-if="diseaseCauseConfig" class="center">
        <h2>{{ $t('admin.diseaseCauseConfig.pageTitle') }} {{ diseaseCauseConfig.name }}</h2>
        <MapManager
            :label="$t('admin.diseaseCauseConfig.diseases')"
            :map="diseaseCauseConfig.diseases"
            mapIndexesType="string"
            @addTuple="addDisease"
            @removeIndex="removeDisease"
        />
        <button class="action-button" type="submit" @click="update">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import { DiseaseCauseConfig } from "@/entities/Config/DiseaseCauseConfig";
import MapManager from "@/components/Utils/MapManager.vue";

interface DiseaseCauseConfigState {
    diseaseCauseConfig: null|DiseaseCauseConfig
    errors: any
}

export default defineComponent({
    name: "DiseaseCauseConfig",
    components: {
        MapManager
    },
    data: function (): DiseaseCauseConfigState {
        return {
            diseaseCauseConfig: null,
            errors: {}
        };
    },
    methods: {
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
