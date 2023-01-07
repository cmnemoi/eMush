<template>
    <div v-if="symptomConfig" class="center">
        <h2>{{ $t('admin.symptomConfig.pageTitle') }} {{ symptomConfig.symptomName }}</h2>
        <div class="flex-row">
            <Input
                :label="$t('admin.symptomConfig.symptomName')"
                id="symptomConfig_symptomName"
                v-model="symptomConfig.symptomName"
                type="text"
                :errors="errors.symptomName"
            ></Input>
            <Input
                :label="$t('admin.symptomConfig.name')"
                id="symptomConfig_name"
                v-model="symptomConfig.name"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.symptomConfig.trigger')"
                id="symptomConfig_trigger"
                v-model="symptomConfig.trigger"
                type="text"
                :errors="errors.trigger"
            ></Input>
            <Input
                :label="$t('admin.symptomConfig.visibility')"
                id="symptomConfig_visibility"
                v-model="symptomConfig.visibility"
                type="text"
                :errors="errors.visibility"
            ></Input>
        </div>
        <h3>{{ $t('admin.symptomConfig.symptomActivationRequirements')}}</h3>
        <ChildCollectionManager :children="symptomConfig.symptomActivationRequirements" @addId="selectNewChild" @remove="removeChild">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <button class="action-button" type="submit" @click="update">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import { SymptomConfig } from "@/entities/Config/SymptomConfig";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { SymptomActivationRequirement } from "@/entities/Config/SymptomActivationRequirement";
import Input from "@/components/Utils/Input.vue";
import { removeItem } from "@/utils/misc";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";

interface SymptomConfigState {
    symptomConfig: null|SymptomConfig
    errors: any
}

export default defineComponent({
    name: "SymptomConfigState",
    components: {
        ChildCollectionManager,
        Input
    },
    data: function (): SymptomConfigState {
        return {
            symptomConfig: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.symptomConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateSymptomConfig(this.symptomConfig)
                .then((res: SymptomConfig | null) => {
                    this.symptomConfig = res;
                    if (this.symptomConfig !== null) {
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'symptom_configs', String(this.symptomConfig.id), 'symptom_activation_requirements'))
                            .then((result) => {
                                const symptomActivationRequirements : SymptomActivationRequirement[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentCondition = (new SymptomActivationRequirement()).load(datum);
                                    symptomActivationRequirements.push(currentCondition);
                                });
                                if (this.symptomConfig instanceof SymptomConfig) {
                                    this.symptomConfig.symptomActivationRequirements = symptomActivationRequirements;
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
            GameConfigService.loadSymptomActivationRequirement(selectedId).then((res) => {
                if (res && this.symptomConfig && this.symptomConfig.symptomActivationRequirements) {
                    this.symptomConfig.symptomActivationRequirements.push(res);
                }
            });
        },
        removeChild(child: any) {
            if (this.symptomConfig && this.symptomConfig.symptomActivationRequirements) {
                this.symptomConfig.symptomActivationRequirements = removeItem(this.symptomConfig.symptomActivationRequirements, child);
            }
        }
    },
    beforeMount() {
        const symptomConfigId = String(this.$route.params.symptomConfigId);
        GameConfigService.loadSymptomConfig(Number(symptomConfigId)).then((res: SymptomConfig | null) => {
            if (res instanceof SymptomConfig) {
                this.symptomConfig = res;
                ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'symptom_configs', symptomConfigId, 'symptom_activation_requirements'))
                    .then((result) => {
                        const symptomActivationRequirements : SymptomActivationRequirement[] = [];
                        result.data['hydra:member'].forEach((datum: any) => {
                            const currentCondition = (new SymptomActivationRequirement()).load(datum);
                            symptomActivationRequirements.push(currentCondition);
                        });
                        if (this.symptomConfig instanceof SymptomConfig) {
                            this.symptomConfig.symptomActivationRequirements = symptomActivationRequirements;
                        }
                    });
            }
        });

    }
});
</script>


<style lang="scss" scoped>

</style>
