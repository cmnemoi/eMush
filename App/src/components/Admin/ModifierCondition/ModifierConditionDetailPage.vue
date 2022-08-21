<template>
    <div v-if="modifierCondition" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.modifierCondition.name')"
                id="modifierConfig_name"
                v-model="modifierCondition.name"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.modifierCondition.condition')"
                id="modifierCondition_condition"
                v-model="modifierCondition.condition"
                type="text"
                :errors="errors.condition"
            ></Input>
            <Input
                :label="$t('admin.modifierCondition.value')"
                id="modifierCondition_value"
                v-model="modifierCondition.value"
                type="text"
                :errors="errors.value"
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
import { ModifierCondition } from "@/entities/Config/ModifierCondition";
import Input from "@/components/Utils/Input.vue";

interface ModifierConditionState {
    modifierCondition: null|ModifierCondition
    errors: any
}

export default defineComponent({
    name: "ModifierCondition",
    components: {
        Input
    },
    data: function (): ModifierConditionState {
        return {
            modifierCondition: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.modifierCondition === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateModifierCondition(this.modifierCondition)
                .then((res: ModifierCondition | null) => {
                    this.modifierCondition = res;
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
        const modifierConditionId = Number(this.$route.params.modifierConditionId);
        GameConfigService.loadModifierCondition(modifierConditionId).then((res: ModifierCondition | null) => {
            this.modifierCondition = res;
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
