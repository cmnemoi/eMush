<template>
    <div v-if="actionConfig" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.actionConfig.name')"
                id="actionConfig_name"
                v-model="actionConfig.name"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.actionConfig.target')"
                id="actionConfig_target"
                v-model="actionConfig.target"
                type="text"
                :errors="errors.target"
            ></Input>
            <Input
                :label="$t('admin.actionConfig.types')"
                id="actionConfig_types"
                v-model="actionConfig.types"
                type="text"
                :errors="errors.types"
            ></Input>
            <Input
                :label="$t('admin.actionConfig.scope')"
                id="actionConfig_scope"
                v-model="actionConfig.scope"
                type="text"
                :errors="errors.scope"
            ></Input>
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.actionConfig.successRate')"
                id="actionConfig_successRate"
                v-model="actionConfig.successRate"
                type="number"
                :errors="errors.successRate"
            ></Input>
            <Input
                :label="$t('admin.actionConfig.injuryRate')"
                id="actionConfig_injuryRate"
                v-model="actionConfig.injuryRate"
                type="number"
                :errors="errors.injuryRate"
            ></Input>
            <Input
                :label="$t('admin.actionConfig.dirtyRate')"
                id="actionConfig_dirtyRate"
                v-model="actionConfig.dirtyRate"
                type="number"
                :errors="errors.dirtyRate"
            ></Input>
        </div>
        <button class="action-button" type="submit" @click="update">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import { ActionConfig } from "@/entities/Config/ActionConfig";

interface ActionConfigState {
    actionConfig: null|ActionConfig
    errors: any
}

export default defineComponent({
    name: "ActionConfigDetailPage",
    components: {
        Input
    },
    data: function (): ActionConfigState {
        return {
            actionConfig: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.actionConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateActionConfig(this.actionConfig)
                .then((res: ActionConfig | null) => {
                    this.actionConfig = res;
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
        const actionConfigId = Number(this.$route.params.actionConfigId);
        GameConfigService.loadActionConfig(actionConfigId).then((res: ActionConfig | null) => {
            this.actionConfig = res;
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
