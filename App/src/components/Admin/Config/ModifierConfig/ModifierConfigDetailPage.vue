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
                :label="$t('admin.modifierConfig.delta')"
                id="modifierConfig_delta"
                v-model="modifierConfig.delta"
                type="text"
                :errors="errors.delta"
            />
            <Input
                :label="$t('admin.modifierConfig.target')"
                id="modifierConfig_target"
                v-model="modifierConfig.target"
                type="text"
                :errors="errors.target"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.modifierConfig.scope')"
                id="modifierConfig_scope"
                v-model="modifierConfig.scope"
                type="text"
                :errors="errors.scope"
            />
            <Input
                :label="$t('admin.modifierConfig.reach')"
                id="modifierConfig_reach"
                v-model="modifierConfig.reach"
                type="text"
                :errors="errors.reach"
            />
            <Input
                :label="$t('admin.modifierConfig.mode')"
                id="modifierConfig_mode"
                v-model="modifierConfig.mode"
                type="text"
                :errors="errors.mode"
            />
        </div>
        <h3>Modifier Condition</h3>
        <ChildCollectionManager :children="modifierConfig.modifierConditions" @addId="selectNewChild" @remove="removeChild">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
            <template #body="child">
                <span>name: {{ child.name }}</span>
                <span>condition: {{ child.condition }}</span>
                <span>value: {{ child.value }}</span>
            </template>
        </ChildCollectionManager>
        <button class="action-button" type="submit" @click="update">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { ModifierCondition } from "@/entities/Config/ModifierCondition";
import Input from "@/components/Utils/Input.vue";
import { removeItem } from "@/utils/misc";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";

interface ModifierConfigState {
    modifierConfig: null|ModifierConfig
    errors: any
}

export default defineComponent({
    name: "ModifierConfigState",
    components: {
        ChildCollectionManager,
        Input,
    },
    data: function (): ModifierConfigState {
        return {
            modifierConfig: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.modifierConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateModifierConfig(this.modifierConfig)
                .then((res: ModifierConfig | null) => {
                    this.modifierConfig = res;
                    if (this.modifierConfig !== null) {
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'modifier_configs', String(this.modifierConfig.id), 'modifier_conditions'))
                            .then((result) => {
                                const modifierConditions : ModifierCondition[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentCondition = (new ModifierCondition()).load(datum);
                                    modifierConditions.push(currentCondition);
                                });
                                if (this.modifierConfig instanceof ModifierConfig) {
                                    this.modifierConfig.modifierConditions = modifierConditions;
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
            GameConfigService.loadModifierCondition(selectedId).then((res) => {
                if (res && this.modifierConfig && this.modifierConfig.modifierConditions) {
                    this.modifierConfig.modifierConditions.push(res);
                }
            });
        },
        removeChild(child: any) {
            if (this.modifierConfig && this.modifierConfig.modifierConditions) {
                this.modifierConfig.modifierConditions = removeItem(this.modifierConfig.modifierConditions, child);
            }
        }
    },
    beforeMount() {
        const modifierConfigId = String(this.$route.params.modifierConfigId);
        GameConfigService.loadModifierConfig(Number(modifierConfigId)).then((res: ModifierConfig | null) => {
            if (res instanceof ModifierConfig) {
                this.modifierConfig = res;
                ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'modifier_configs', modifierConfigId, 'modifier_conditions'))
                    .then((result) => {
                        const modifierConditions : ModifierCondition[] = [];
                        result.data['hydra:member'].forEach((datum: any) => {
                            const currentCondition = (new ModifierCondition()).load(datum);
                            modifierConditions.push(currentCondition);
                        });
                        if (this.modifierConfig instanceof ModifierConfig) {
                            this.modifierConfig.modifierConditions = modifierConditions;
                        }
                    });
            }
        });

    }
});
</script>


<style lang="scss" scoped>

</style>
