<template>
    <div v-if="symptomActivationRequirement" class="center">
        <h3>{{ $t('admin.symptomActivationRequirement.pageTitle') }} {{ symptomActivationRequirement.name }}</h3>
        <div class="flex-row">
            <Input
                :label="$t('admin.symptomActivationRequirement.name')"
                id="symptomActivationRequirement_name"
                v-model="symptomActivationRequirement.name"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.symptomActivationRequirement.activationRequirementName')"
                id="symptomActivationRequirement_activationRequirementName"
                v-model="symptomActivationRequirement.activationRequirementName"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.symptomActivationRequirement.activationRequirement')"
                id="symptomActivationRequirement_activationRequirement"
                v-model="symptomActivationRequirement.activationRequirement"
                type="text"
                :errors="errors.activationRequirement"
            ></Input>
            <Input
                :label="$t('admin.symptomActivationRequirement.value')"
                id="symptomActivationRequirement_value"
                v-model="symptomActivationRequirement.value"
                type="number"
                :errors="errors.value"
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
import { SymptomActivationRequirement } from "@/entities/Config/SymptomActivationRequirement";
import Input from "@/components/Utils/Input.vue";

interface SymptomActivationRequirementState {
    symptomActivationRequirement: null|SymptomActivationRequirement
    errors: any
}

export default defineComponent({
    name: "SymptomActivationRequirement",
    components: {
        Input
    },
    data: function (): SymptomActivationRequirementState {
        return {
            symptomActivationRequirement: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.symptomActivationRequirement === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateSymptomActivationRequirement(this.symptomActivationRequirement)
                .then((res: SymptomActivationRequirement | null) => {
                    this.symptomActivationRequirement = res;
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
        const symptomActivationRequirementId = Number(this.$route.params.symptomActivationRequirementId);
        GameConfigService.loadSymptomActivationRequirement(symptomActivationRequirementId).then((res: SymptomActivationRequirement | null) => {
            this.symptomActivationRequirement = res;
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
