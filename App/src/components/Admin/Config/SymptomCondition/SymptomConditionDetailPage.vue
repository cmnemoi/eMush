<template>
    <div v-if="symptomCondition" class="center">
        <h3>{{ $t('admin.symptomCondition.pageTitle') }} {{ symptomCondition.name }}</h3>
        <div class="flex-row">
            <Input
                :label="$t('admin.symptomCondition.name')"
                id="symptomConfig_name"
                v-model="symptomCondition.name"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.symptomCondition.conditionName')"
                id="symptomConfig_conditionName"
                v-model="symptomCondition.conditionName"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.symptomCondition.condition')"
                id="symptomCondition_condition"
                v-model="symptomCondition.condition"
                type="text"
                :errors="errors.condition"
            ></Input>
            <Input
                :label="$t('admin.symptomCondition.value')"
                id="symptomCondition_value"
                v-model="symptomCondition.value"
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
import { SymptomCondition } from "@/entities/Config/SymptomCondition";
import Input from "@/components/Utils/Input.vue";

interface SymptomConditionState {
    symptomCondition: null|SymptomCondition
    errors: any
}

export default defineComponent({
    name: "SymptomCondition",
    components: {
        Input
    },
    data: function (): SymptomConditionState {
        return {
            symptomCondition: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.symptomCondition === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateSymptomCondition(this.symptomCondition)
                .then((res: SymptomCondition | null) => {
                    this.symptomCondition = res;
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
        const symptomConditionId = Number(this.$route.params.symptomConditionId);
        GameConfigService.loadSymptomCondition(symptomConditionId).then((res: SymptomCondition | null) => {
            this.symptomCondition = res;
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
