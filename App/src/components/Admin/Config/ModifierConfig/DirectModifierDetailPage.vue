<template>
    <div v-if="modifierConfig" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.modifierConfig.name')"
                id="modifierConfig_name"
                v-model="modifierConfig.name"
                type="text"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.modifierConfig.modifierName')"
                id="modifierConfig_modifierName"
                v-model="modifierConfig.modifierName"
                type="text"
                :errors="errors.modifierName"
            />
            <Input
                :label="$t('admin.modifierConfig.modifierRange')"
                id="modifierConfig_modifierRange"
                v-model="modifierConfig.modifierRange"
                type="text"
                :errors="errors.modifierRange"
            />
            <Input
                :label="$t('admin.modifierConfig.reverseOnRemove')"
                type="checkbox"
                class="configCheckbox"
                id="modifierConfig_reverseOnRemove"
                v-model="modifierConfig.reverseOnRemove"
            />
        </div>
        <h3>{{ $t("admin.modifierConfig.triggeredEvent") }}</h3>
        <ChildManager :child="modifierConfig.triggeredEvent" @addId="selectNewEventConfig">
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildManager>
        <h3>{{ $t("admin.modifierConfig.modifierRequirement") }}</h3>
        <ChildCollectionManager :children="modifierConfig.modifierActivationRequirements" @addId="selectNewChild" @remove="removeChild">
            <template #header="child">
                <span><strong>{{ child.id }}</strong> - {{ child.modifierName }}</span>
            </template>
            <template #body="child">
                <span>name: {{ child.modifierName }}</span>
                <span>activationRequirement: {{ child.activationRequirement }}</span>
                <span>value: {{ child.value }}</span>
            </template>
        </ChildCollectionManager>
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { ModifierActivationRequirement } from "@/entities/Config/ModifierActivationRequirement";
import Input from "@/components/Utils/Input.vue";
import { removeItem } from "@/utils/misc";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import { EventConfig } from "@/entities/Config/EventConfig";
import ChildManager from "@/components/Utils/ChildManager.vue";

interface ModifierConfigState {
    modifierConfig: null|ModifierConfig
    errors: any
}

export default defineComponent({
    name: "TriggerEventModifierConfigState",
    components: {
        ChildManager,
        ChildCollectionManager,
        Input,
        UpdateConfigButtons
    },
    data: function (): ModifierConfigState {
        return {
            modifierConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if (this.modifierConfig === null) return;

            const newModifierConfig = this.modifierConfig;
            newModifierConfig.id = null;

            GameConfigService.createModifierConfig(newModifierConfig)
                .then((res: ModifierConfig | null) => {
                    const newModifierConfigUrl = urlJoin(process.env.VUE_APP_URL + '/config/modifier-config', String(res?.id));
                    window.location.href = newModifierConfigUrl;
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
            if (this.modifierConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateModifierConfig(this.modifierConfig)
                .then((res: ModifierConfig | null) => {
                    this.modifierConfig = res;
                    if (this.modifierConfig !== null) {
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'modifier_configs', String(this.modifierConfig.id), 'modifier_activation_requirements'))
                            .then((result) => {
                                const modifierActivationRequirements : ModifierActivationRequirement[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentCondition = (new ModifierActivationRequirement()).load(datum);
                                    modifierActivationRequirements.push(currentCondition);
                                });
                                if (this.modifierConfig instanceof ModifierConfig) {
                                    this.modifierConfig.modifierActivationRequirements = modifierActivationRequirements;
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
            GameConfigService.loadModifierActivationRequirement(selectedId).then((res) => {
                if (res && this.modifierConfig && this.modifierConfig.modifierActivationRequirements) {
                    this.modifierConfig.modifierActivationRequirements.push(res);
                }
            });
        },
        removeChild(child: any) {
            if (this.modifierConfig && this.modifierConfig.modifierActivationRequirements) {
                this.modifierConfig.modifierActivationRequirements = removeItem(this.modifierConfig.modifierActivationRequirements, child);
            }
        },
        selectNewEventConfig(selectedId: integer){
            GameConfigService.loadEventConfig(selectedId).then((res) => {
                if (res && this.modifierConfig){
                    this.modifierConfig.triggeredEvent = res;
                }
            });
        },
    },
    beforeMount() {
        const modifierConfigId = String(this.$route.params.configId);
        GameConfigService.loadModifierConfig(Number(modifierConfigId)).then((res: ModifierConfig | null) => {
            if (res instanceof ModifierConfig) {
                this.modifierConfig = res;
                ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'direct_modifier_configs', modifierConfigId, 'modifier_activation_requirements'))
                    .then((result) => {
                        const modifierActivationRequirements : ModifierActivationRequirement[] = [];
                        result.data['hydra:member'].forEach((datum: any) => {
                            const currentRequirement = (new ModifierActivationRequirement()).load(datum);
                            modifierActivationRequirements.push(currentRequirement);
                        });
                        if (this.modifierConfig instanceof ModifierConfig) {
                            this.modifierConfig.modifierActivationRequirements = modifierActivationRequirements;
                        }
                    });

                ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'direct_modifier_configs', modifierConfigId, 'triggered_event'))
                    .then((result) => {
                        const eventConfig: EventConfig = new EventConfig();
                        eventConfig.load(result.data);

                        if (this.modifierConfig instanceof ModifierConfig) {
                            this.modifierConfig.triggeredEvent = eventConfig;
                        }
                    });
            }
        });

    }
});
</script>


<style lang="scss" scoped>

</style>
