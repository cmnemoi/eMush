<template>
    <div v-if="modifierActivationRequirement" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.modifierActivationRequirement.name')"
                id="modifierConfig_name"
                v-model="modifierActivationRequirement.name"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.modifierActivationRequirement.activationRequirement')"
                id="modifierActivationRequirement_activationRequirement"
                v-model="modifierActivationRequirement.activationRequirement"
                type="text"
                :errors="errors.activationRequirement"
            ></Input>
            <Input
                :label="$t('admin.modifierActivationRequirement.value')"
                id="modifierActivationRequirement_value"
                v-model="modifierActivationRequirement.value"
                type="text"
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
import { ModifierActivationRequirement } from "@/entities/Config/ModifierActivationRequirement";
import Input from "@/components/Utils/Input.vue";

interface ModifierActivationRequirementState {
    modifierActivationRequirement: null|ModifierActivationRequirement
    errors: any
}

export default defineComponent({
    name: "ModifierActivationRequirement",
    components: {
        Input
    },
    data: function (): ModifierActivationRequirementState {
        return {
            modifierActivationRequirement: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.modifierActivationRequirement === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateModifierActivationRequirement(this.modifierActivationRequirement)
                .then((res: ModifierActivationRequirement | null) => {
                    this.modifierActivationRequirement = res;
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
        const modifierActivationRequirementId = Number(this.$route.params.modifierActivationRequirementId);
        GameConfigService.loadModifierActivationRequirement(modifierActivationRequirementId).then((res: ModifierActivationRequirement | null) => {
            this.modifierActivationRequirement = res;
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
