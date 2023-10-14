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
                :label="$t('admin.actionConfig.actionName')"
                id="actionConfig_actionName"
                v-model="actionConfig.actionName"
                type="text"
                :errors="errors.actionName"
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
                :label="$t('admin.actionConfig.actionPoint')"
                id="actionConfig_actionPoint"
                v-model="actionConfig.actionVariablesArray.actionPoint"
                type="number"
                :errors="errors.actionPoint"
            ></Input>
            <Input
                :label="$t('admin.actionConfig.movementPoint')"
                id="actionConfig_movementPoint"
                v-model="actionConfig.actionVariablesArray.movementPoint"
                type="number"
                :errors="errors.movementPoint"
            ></Input>
            <Input
                :label="$t('admin.actionConfig.moralPoint')"
                id="actionConfig_moralPoint"
                v-model="actionConfig.actionVariablesArray.moralPoint"
                type="number"
                :errors="errors.moralPoint"
            ></Input>
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.actionConfig.percentageSuccess')"
                id="actionConfig_percentageSuccess"
                v-model="actionConfig.actionVariablesArray.percentageSuccess"
                type="number"
                :errors="errors.percentageSuccess"
            ></Input>
            <Input
                :label="$t('admin.actionConfig.percentageCritical')"
                id="actionConfig_percentageCritical"
                v-model="actionConfig.actionVariablesArray.percentageCritical"
                type="number"
                :errors="errors.percentageCritical"
            ></Input>
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.actionConfig.percentageInjury')"
                id="actionConfig_percentageInjury"
                v-model="actionConfig.actionVariablesArray.percentageInjury"
                type="number"
                :errors="errors.percentageInjury"
            ></Input>
            <Input
                :label="$t('admin.actionConfig.percentageDirtiness')"
                id="actionConfig_dirtyRate"
                v-model="actionConfig.actionVariablesArray.percentageDirtiness"
                type="number"
                :errors="errors.percentageDirtiness"
            ></Input>
            <input
                type="checkbox"
                class="actionConfigCheckbox"
                id="actionConfig_isSuperDirty"
                v-model="actionConfig.actionVariablesArray.isSuperDirty"
            />
            <label for="actionConfig_isSuperDirty">{{ actionConfig.actionVariablesArray.isSuperDirty ? $t('admin.actionConfig.isSuperDirty') : $t('admin.actionConfig.isNotSuperDirty') }}</label>

        </div>
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import { ActionConfig } from "@/entities/Config/ActionConfig";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import urlJoin from "url-join";

interface ActionConfigState {
    actionConfig: null|ActionConfig
    errors: any
}

export default defineComponent({
    name: "ActionConfigDetailPage",
    components: {
        Input,
        UpdateConfigButtons
    },
    data: function (): ActionConfigState {
        return {
            actionConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if (this.actionConfig === null) return;

            const newActionConfig = this.actionConfig;
            newActionConfig.id = null;

            // @ts-ignore
            GameConfigService.createActionConfig(newActionConfig)
                .then((res: ActionConfig | null) => {
                    const newActionConfigUrl = urlJoin(import.meta.env.VITE_URL + '/config/action-config', String(res?.id));
                    window.location.href = newActionConfigUrl;
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
