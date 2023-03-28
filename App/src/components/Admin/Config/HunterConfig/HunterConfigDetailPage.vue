<template>
    <div v-if="hunterConfig" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.hunterConfig.name')"
                id="hunterConfig_name"
                v-model="hunterConfig.name"
                type="text"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.hunterConfig.hunterName')"
                id="hunterConfig_hunterName"
                v-model="hunterConfig.hunterName"
                type="text"
                :errors="errors.hunterName"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.hunterConfig.initialHealth')"
                id="hunterConfig_initialHealth"
                v-model="hunterConfig.initialHealth"
                type="number"
                :errors="errors.initialHealth"
            />
            <Input
                :label="$t('admin.hunterConfig.initialCharge')"
                id="hunterConfig_initialCharge"
                v-model="hunterConfig.initialCharge"
                type="number"
                :errors="errors.initialCharge"
            />
            <Input
                :label="$t('admin.hunterConfig.initialArmor')"
                id="hunterConfig_initialArmor"
                v-model="hunterConfig.initialArmor"
                type="number"
                :errors="errors.initialArmor"
            />
            <Input
                :label="$t('admin.hunterConfig.minDamage')"
                id="hunterConfig_minDamage"
                v-model="hunterConfig.minDamage"
                type="number"
                :errors="errors.minDamage"
            />
            <Input
                :label="$t('admin.hunterConfig.maxDamage')"
                id="hunterConfig_maxDamage"
                v-model="hunterConfig.maxDamage"
                type="number"
                :errors="errors.maxDamage"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.hunterConfig.hitChance')"
                id="hunterConfig_hitChance"
                v-model="hunterConfig.hitChance"
                type="number"
                :errors="errors.hitChance"
            />
            <Input
                :label="$t('admin.hunterConfig.dodgeChance')"
                id="hunterConfig_dodgeChance"
                v-model="hunterConfig.dodgeChance"
                type="number"
                :errors="errors.dodgeChance"
            />
            <Input
                :label="$t('admin.hunterConfig.drawCost')"
                id="hunterConfig_drawCost"
                v-model="hunterConfig.drawCost"
                type="number"
                :errors="errors.drawCost"
            />
            <Input
                :label="$t('admin.hunterConfig.maxPerWave')"
                id="hunterConfig_maxPerWave"
                v-model="hunterConfig.maxPerWave"
                type="number"
                :errors="errors.maxPerWave"
            />
            <Input
                :label="$t('admin.hunterConfig.drawWeight')"
                id="hunterConfig_drawWeight"
                v-model="hunterConfig.drawWeight"
                type="number"
                :errors="errors.drawWeight"
            />
        </div>
    </div>
    <UpdateConfigButtons
        @create="create"
        @update="update"
    />
</template>

<script lang="ts">
import ApiService from "@/services/api.service";
import { defineComponent } from "vue";
import { handleErrors } from "@/utils/apiValidationErrors";
import urlJoin from "url-join";
import Input from "@/components/Utils/Input.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import HunterConfigService from "@/services/hunter.config.service";
import { HunterConfig } from "@/entities/Config/HunterConfig";


interface HunterConfigState {
    hunterConfig: null|HunterConfig
    errors: any
}

export default defineComponent({
    name: "HunterConfigDetailPage",
    components: {
        Input,
        UpdateConfigButtons
    },
    data: function (): HunterConfigState {
        return {
            hunterConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            const newHunterConfig = (new HunterConfig()).load(this.hunterConfig?.jsonEncode());
            
            newHunterConfig.id = null;
            HunterConfigService.createHunterConfig(newHunterConfig).then((res: HunterConfig | null) => {
                this.hunterConfig = res;
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
            if (this.hunterConfig === null) {
                return;
            }
            this.errors = {};
            HunterConfigService.updateHunterConfig(this.hunterConfig)
                .then((res: HunterConfig | null) => {
                    this.hunterConfig = res;
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
        const hunterConfigId = Number(this.$route.params.hunterConfigId);
        console.log(hunterConfigId);
        HunterConfigService.loadHunterConfig(hunterConfigId).then((res: HunterConfig | null) => {
            this.hunterConfig = res;
        });     
    }
});
</script>

<style lang="scss" scoped>

</style>
