<template>
    <div v-if="modifierConfig" class="center">
        <div class="flex-row">
        </div>
        <div class="flex-row">
            <div class="flex-grow-1">
                <label for="modifierConfig_name">{{ $t('modifierConfig.name') }}</label>
                <input
                    id="modifierConfig_name"
                    ref="modifierConfig_name"
                    v-model="modifierConfig.name"
                    type="text"
                >
                <ErrorList v-if="errors.name" :errors="errors.name"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="modifierConfig_delta">{{ $t('modifierConfig.delta') }}</label>
                <input
                    id="modifierConfig_delta"
                    ref="modifierConfig_delta"
                    v-model="modifierConfig.delta"
                    type="text"
                >
                <ErrorList v-if="errors.delta" :errors="errors.delta"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="modifierConfig_target">{{ $t('modifierConfig.target') }}</label>
                <input
                    id="modifierConfig_target"
                    ref="modifierConfig_target"
                    v-model="modifierConfig.target"
                    type="text"
                >
                <ErrorList v-if="errors.target" :errors="errors.target"></ErrorList>
            </div>
        </div>
        <div class="flex-row">
            <div class="flex-grow-1">
                <label for="modifierConfig_scope">{{ $t('modifierConfig.scope') }}</label>
                <input
                    id="modifierConfig_scope"
                    ref="modifierConfig_scope"
                    v-model="modifierConfig.scope"
                    type="text"
                >
                <ErrorList v-if="errors.scope" :errors="errors.scope"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="modifierConfig_reach">{{ $t('modifierConfig.reach') }}</label>
                <input
                    id="modifierConfig_reach"
                    ref="modifierConfig_reach"
                    v-model="modifierConfig.reach"
                    type="text"
                >
                <ErrorList v-if="errors.reach" :errors="errors.reach"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="modifierConfig_mode">{{ $t('modifierConfig.mode') }}</label>
                <input
                    id="modifierConfig_mode"
                    ref="modifierConfig_mode"
                    v-model="modifierConfig.mode"
                    type="text"
                >
                <ErrorList v-if="errors.mode" :errors="errors.mode"></ErrorList>
            </div>
        </div>
        <button class="button" type="submit" @click="update">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { mapGetters } from "vuex";
import GameConfigService from "@/services/game_config.service";
import ErrorList from "@/components/Utils/ErrorList.vue";
import { handleErrors } from "@/utils/apiValidationErrors";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";

interface ModifierConfigState {
    modifierConfig: null|ModifierConfig
    errors: any
}

export default defineComponent({
    name: "ModifierConfigState",
    components: {
        ErrorList
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
    computed: {
        ...mapGetters('auth', [
            'loggedIn',
            'getUserInfo'
        ])
    },
    beforeMount() {
        const modifierConfigId = Number(this.$route.params.modifierConfigId);
        GameConfigService.loadModifierConfig(modifierConfigId).then((res: ModifierConfig | null) => {
            this.modifierConfig = res;
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
