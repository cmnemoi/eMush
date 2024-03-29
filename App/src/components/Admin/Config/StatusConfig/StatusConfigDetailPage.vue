<template>
    <div v-if="statusConfig" class="center">
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.statusConfig.name')"
                id="statusConfig_name"
                v-model="statusConfig.name"
                type="text"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.statusConfig.statusName')"
                id="statusConfig_statusName"
                v-model="statusConfig.statusName"
                type="text"
                :errors="errors.statusName"
            />
            <Input
                :label="$t('admin.statusConfig.visibility')"
                id="statusConfig_visibility"
                v-model="statusConfig.visibility"
                type="text"
                :errors="errors.visibility"
            />
        </div>
        <template v-if="statusConfig.configType === 'ChargeStatusConfig'">
            <h3>Charge Status Config</h3>
            <div class="flex-row">
                <Input
                    :label="$t('admin.statusConfig.chargeVisibility')"
                    id="statusConfig_chargeVisibility"
                    v-model="statusConfig.chargeVisibility"
                    type="text"
                    :errors="errors.chargeVisibility"
                />
                <Input
                    :label="$t('admin.statusConfig.chargeStrategy')"
                    id="statusConfig_chargeStrategy"
                    v-model="statusConfig.chargeStrategy"
                    type="text"
                    :errors="errors.chargeStrategy"
                />
            </div>
            <div class="flex-row">
                <Input
                    :label="$t('admin.statusConfig.maxCharge')"
                    id="statusConfig_maxCharge"
                    v-model="statusConfig.maxCharge"
                    type="number"
                    :errors="errors.maxCharge"
                />
                <Input
                    :label="$t('admin.statusConfig.startCharge')"
                    id="statusConfig_startCharge"
                    v-model="statusConfig.startCharge"
                    type="number"
                    :errors="errors.startCharge"
                />
                <div class="checkbox-container">
                    <input
                        type="checkbox"
                        id="statusConfig_autoRemove"
                        v-model="statusConfig.autoRemove"
                    />
                    <label for="statusConfig_autoRemove">{{ statusConfig.autoRemove ? 'Auto-remove' : 'No Auto-remove' }}</label>
                </div>
            </div>
        </template>

        <h3>Modifier Configs</h3>
        <ChildCollectionManager
            :children="statusConfig.modifierConfigs"
            id="statusConfig_modifierConfigs"
            @addId="selectNewChild"
            @remove="removeChild"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
            <template #body="child">
                <span>name: {{ child.name }}</span>
                <span>condition: {{ child.delta }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t('admin.statusConfig.dischargeStrategies') }}</h3>
        <StringArrayManager
            :array="statusConfig.dischargeStrategies"
            id="statusConfig_dischargeStrategies"
            @addElement="statusConfig.dischargeStrategies?.push($event)"
            @removeElement="statusConfig.dischargeStrategies?.splice(statusConfig.dischargeStrategies.indexOf($event), 1)"
        />
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import { removeItem } from "@/utils/misc";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import StringArrayManager from "@/components/Utils/StringArrayManager.vue";

interface StatusConfigState {
    statusConfig: null|StatusConfig
    errors: any
}

export default defineComponent({
    name: "StatusConfigDetailPage",
    components: {
        ChildCollectionManager,
        Input,
        UpdateConfigButtons,
        StringArrayManager
    },
    data: function (): StatusConfigState {
        return {
            statusConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if (this.statusConfig === null) return;

            const newStatusConfig = this.statusConfig;
            newStatusConfig.id = null;

            GameConfigService.createStatusConfig(newStatusConfig)
                .then((res: StatusConfig | null) => {
                    const newStatusConfigUrl = urlJoin(import.meta.env.VITE_APP_URL + '/config/status-config', String(res?.id));
                    window.location.href = newStatusConfigUrl;
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
            if (this.statusConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateStatusConfig(this.statusConfig)
                .then((res: StatusConfig | null) => {
                    this.statusConfig = res;
                    if (this.statusConfig !== null) {
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'status_configs', String(this.statusConfig.id), 'modifier_configs'))
                            .then((result) => {
                                const modifierConfigs: ModifierConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentModifierConfig = (new ModifierConfig()).load(datum);
                                    modifierConfigs.push(currentModifierConfig);
                                });
                                if (this.statusConfig instanceof StatusConfig) {
                                    this.statusConfig.modifierConfigs = modifierConfigs;
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
        selectNewChild(selectedId: any) {
            GameConfigService.loadModifierConfig(selectedId).then((res) => {
                if (res && this.statusConfig && this.statusConfig.modifierConfigs) {
                    this.statusConfig.modifierConfigs.push(res);
                }
            });
        },
        removeChild(child: any) {
            if (this.statusConfig && this.statusConfig.modifierConfigs) {
                this.statusConfig.modifierConfigs = removeItem(this.statusConfig.modifierConfigs, child);
            }
        }
    },
    beforeMount() {
        const statusConfigId = String(this.$route.params.statusConfigId);
        GameConfigService.loadStatusConfig(Number(statusConfigId)).then((res: StatusConfig | null) => {
            this.statusConfig = res;
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'status_configs', statusConfigId, 'modifier_configs'))
                .then((result) => {
                    const modifierConfigs : ModifierConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentModifierConfig = (new ModifierConfig()).load(datum);
                        modifierConfigs.push(currentModifierConfig);
                    });
                    if (this.statusConfig instanceof StatusConfig) {
                        this.statusConfig.modifierConfigs = modifierConfigs;
                    }
                });
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
