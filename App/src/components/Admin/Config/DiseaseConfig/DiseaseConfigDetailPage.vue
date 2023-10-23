<template>
    <div v-if="diseaseConfig" class="center">
        <h2>{{ $t("admin.diseaseConfig.pageTitle") }} <em>{{ diseaseConfig.diseaseName }}</em></h2>
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.diseaseConfig.diseaseName')"
                id="diseaseConfig_diseaseName"
                v-model="diseaseConfig.diseaseName"
                type="text"
                :errors="errors.diseaseName"
            />
            <Input
                :label="$t('admin.diseaseConfig.name')"
                id="diseaseConfig_name"
                v-model="diseaseConfig.name"
                type="text"
                :errors="errors.name"
            />


            <Input
                :label="$t('admin.diseaseConfig.type')"
                id="diseaseConfig_type"
                v-model="diseaseConfig.type"
                type="text"
                :errors="errors.type"
            ></Input>
            <Input
                :label="$t('admin.diseaseConfig.resistance')"
                id="diseaseConfig_resistance"
                v-model="diseaseConfig.resistance"
                type="number"
                :errors="errors.resistance"
            ></Input>
            <Input
                :label="$t('admin.diseaseConfig.delayMin')"
                id="diseaseConfig_delayMin"
                v-model="diseaseConfig.delayMin"
                type="number"
                :errors="errors.delayMin"
            ></Input>

            <Input
                :label="$t('admin.diseaseConfig.delayLength')"
                id="diseaseConfig_delayLength"
                v-model="diseaseConfig.delayLength"
                type="number"
                :errors="errors.delayLength"
            ></Input>
            <Input
                :label="$t('admin.diseaseConfig.diseasePointMin')"
                id="diseaseConfig_diseasePointMin"
                v-model="diseaseConfig.diseasePointMin"
                type="number"
                :errors="errors.diseasePointMin"
            ></Input>
            <Input
                :label="$t('admin.diseaseConfig.diseasePointLength')"
                id="diseaseConfig_diseasePointLength"
                v-model="diseaseConfig.diseasePointLength"
                type="number"
                :errors="errors.diseasePointLength"
            ></Input>
        </div>
        <h3>{{ $t('admin.diseaseConfig.override') }}</h3>
        <StringArrayManager
            :label="$t('admin.diseaseConfig.overrideDiseaseToAdd')"
            :array="diseaseConfig.override"
            id="diseaseConfig_overrideDiseaseToAdd"
            @addElement="diseaseConfig.override?.push($event)"
            @removeElement="diseaseConfig.override?.splice(diseaseConfig.override.indexOf($event), 1)"
        />
        <h3>{{ $t('admin.diseaseConfig.modifierConfigs') }}</h3>
        <ChildCollectionManager
            :children="diseaseConfig.modifierConfigs"
            id="diseaseConfig_modifierConfigs"
            @addId="selectNewModifierConfig"
            @remove="removeModifierConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
            <template #body="child">
                <span>{{ $t('admin.modifierConfig.target') }}: {{ child.target }}</span>
                <span>{{ $t('admin.modifierConfig.delta')  }}: {{ child.delta }}</span>
                <span>{{ $t('admin.modifierConfig.scope') }}: {{ child.scope }}</span>
                <span>{{ $t('admin.modifierConfig.reach') }}: {{ child.reach }}</span>
                <span>{{ $t('admin.modifierConfig.mode') }}: {{ child.mode }}</span>
            </template>
        </ChildCollectionManager>
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import { DiseaseConfig } from "@/entities/Config/DiseaseConfig";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import Input from "@/components/Utils/Input.vue";
import { removeItem } from "@/utils/misc";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import StringArrayManager from "@/components/Utils/StringArrayManager.vue";

interface DiseaseConfigState {
    diseaseConfig: null|DiseaseConfig
    errors: any,
    disease: string,
}

export default defineComponent({
    name: "DiseaseConfigDetailPage",
    components: {
        ChildCollectionManager,
        Input,
        UpdateConfigButtons,
        StringArrayManager,
    },
    data: function (): DiseaseConfigState {
        return {
            diseaseConfig: null,
            errors: {},
            disease: '',
        };
    },
    methods: {
        create(): void {
            // @ts-ignore
            const newDiseaseConfig = (new DiseaseConfig()).load(this.diseaseConfig?.jsonEncode());
            
            newDiseaseConfig.id = null;
            if (this.diseaseConfig?.modifierConfigs !== undefined){
                newDiseaseConfig.modifierConfigs = this.diseaseConfig?.modifierConfigs;
            }

            GameConfigService.createDiseaseConfig(newDiseaseConfig).then((res: DiseaseConfig | null) => {
                const newDiseaseConfigUrl = urlJoin(process.env.VUE_APP_URL + '/config/disease-config', String(res?.id));
                window.location.href = newDiseaseConfigUrl;
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
            if (this.diseaseConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateDiseaseConfig(this.diseaseConfig)
                .then((res: DiseaseConfig | null) => {
                    this.diseaseConfig = res;
                    if (this.diseaseConfig !== null){
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'disease_configs', String(this.diseaseConfig.id), 'modifier_configs'))
                            .then((result) => {
                                const modifierConfigs : ModifierConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const modifierConfig = (new ModifierConfig()).load(datum);
                                    modifierConfigs.push(modifierConfig);
                                });
                                if (this.diseaseConfig instanceof DiseaseConfig) {
                                    this.diseaseConfig.modifierConfigs = modifierConfigs;
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
        addNewOverrideDisease(disease: string) {
            if (this.diseaseConfig && this.diseaseConfig.override) {
                this.diseaseConfig.override.push(disease);
            }
        },
        removeNewOverrideDisease(disease: string) {
            if (this.diseaseConfig && this.diseaseConfig.override) {
                this.diseaseConfig.override = removeItem(this.diseaseConfig.override, disease);
            }
        },
        selectNewModifierConfig(selectedId: any) {
            GameConfigService.loadModifierConfig(selectedId).then((res) => {
                if (res && this.diseaseConfig && this.diseaseConfig.modifierConfigs) {
                    this.diseaseConfig.modifierConfigs.push(res);
                }
            });
        },
        removeModifierConfig(child: any) {
            if (this.diseaseConfig && this.diseaseConfig.modifierConfigs) {
                this.diseaseConfig.modifierConfigs = removeItem(this.diseaseConfig.modifierConfigs, child);
            }
        },
    },
    beforeMount() {
        const diseaseConfigId = String(this.$route.params.diseaseConfigId);
        GameConfigService.loadDiseaseConfig(Number(diseaseConfigId)).then((res: DiseaseConfig | null) => {
            this.diseaseConfig = res;
            if (res instanceof DiseaseConfig) {
                this.diseaseConfig = res;
                ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'disease_configs', diseaseConfigId, 'modifier_configs'))
                    .then((result) => {
                        const modifierConfigs : ModifierConfig[] = [];
                        result.data['hydra:member'].forEach((datum: any) => {
                            const currentModifierConfig = (new ModifierConfig()).load(datum);
                            modifierConfigs.push(currentModifierConfig);
                        });
                        if (this.diseaseConfig instanceof DiseaseConfig) {
                            this.diseaseConfig.modifierConfigs = modifierConfigs;
                        }
                    });
            }
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
