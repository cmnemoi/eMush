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
        <button class="action-button" type="submit" @click="update">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";

interface DaedalusConfigState {
    daedalusConfig: null|DaedalusConfig
    errors: any
}

export default defineComponent({
    name: "DaedalusConfigDetailPage",
    components: {
        Input
    },
    data: function (): DaedalusConfigState {
        return {
            daedalusConfig: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.daedalusConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateDaedalusConfig(this.daedalusConfig)
                .then((res: DaedalusConfig | null) => {
                    this.daedalusConfig = res;
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
        const daedalusConfigId = Number(this.$route.params.daedalusConfigId);
        GameConfigService.loadDaedalusConfig(daedalusConfigId).then((res: DaedalusConfig | null) => {
            this.daedalusConfig = res;
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
