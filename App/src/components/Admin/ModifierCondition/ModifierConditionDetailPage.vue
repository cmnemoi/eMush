<template>
    <div v-if="modifierConfig" class="center">
        <div class="flex-row">
        </div>
        <div class="flex-row">
            <div class="flex-grow-1">
                <label for="modifierCondition_name">{{ $t('modifierCondition.name') }}</label>
                <input
                    id="modifierConfig_name"
                    ref="modifierConfig_name"
                    v-model="modifierCondition.name"
                    type="text"
                >
                <ErrorList v-if="errors.name" :errors="errors.name"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="modifierCondition_condition">{{ $t('modifierCondition.condition') }}</label>
                <input
                    id="modifierCondition_condition"
                    ref="modifierCondition_condition"
                    v-model="modifierCondition.condition"
                    type="text"
                >
                <ErrorList v-if="errors.condition" :errors="errors.condition"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="modifierCondition_value">{{ $t('modifierCondition.value') }}</label>
                <input
                    id="modifierCondition_value"
                    ref="modifierCondition_value"
                    v-model="modifierCondition.value"
                    type="text"
                >
                <ErrorList v-if="errors.value" :errors="errors.value"></ErrorList>
            </div>
        </div>
        <button class="button" type="submit" @click="update">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import ErrorList from "@/components/Utils/ErrorList.vue";
import { handleErrors } from "@/utils/apiValidationErrors";
import { ModifierCondition } from "@/entities/Config/ModifierCondition";

interface ModifierConditionState {
    modifierCondition: null|ModifierCondition
    errors: any
}

export default defineComponent({
    name: "ModifierCondition",
    components: {
        ErrorList
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
        const modifierConditionId = Number(this.$route.params.modifierConfigId);
        GameConfigService.loadModifierCondition(modifierConditionId).then((res: ModifierCondition | null) => {
            this.modifierCondition = res;
        });
    }
});
</script>

<style lang="scss" scoped>
button {
    cursor: pointer;
    margin: 0 20px;
    padding: 5px 10px;
    color: white;
    font-size: 1.1em;
    letter-spacing: .06em;

    &:hover,
    &:active {
        color: #dffaff;
        text-shadow: 0 0 1px rgb(255, 255, 255), 0 0 1px rgb(255, 255, 255);
    }
}
</style>
