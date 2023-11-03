<template>
    <div v-if="triumphConfig" class="center">
        <h2>{{ $t('admin.triumphConfig.pageTitle') }} <em>{{ triumphConfig.name }}</em></h2>
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.triumphConfig.name')"
                id="triumphConfig_name"
                v-model="triumphConfig.name"
                type="text"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.triumphConfig.triumph')"
                id="triumphConfig_triumph"
                v-model="triumphConfig.triumph"
                type="number"
                :errors="errors.triumph"
            />
            <Input
                :label="$t('admin.triumphConfig.team')"
                id="triumphConfig_team"
                v-model="triumphConfig.team"
                type="string"
                :errors="errors.team"
            />
            <div class="checkbox-container">
                <input
                    type="checkbox"
                    id="triumphConfig_isAllCrew"
                    v-model="triumphConfig.isAllCrew"
                />
                <label for="triumphConfig_isAllCrew">{{ triumphConfig.isAllCrew ? $t('admin.triumphConfig.isAllCrew') : $t('admin.triumphConfig.isNotAllCrew') }}</label>
            </div>
        </div>
        <UpdateConfigButtons @create="create" @update="update" />
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { TriumphConfig } from "@/entities/Config/TriumphConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";

interface TriumphConfigState {
    triumphConfig: null|TriumphConfig
    errors: any
}

export default defineComponent({
    name: "TriumphConfigDetailPage",
    components: {
        Input,
        UpdateConfigButtons
    },
    data: function (): TriumphConfigState {
        return {
            triumphConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            // @ts-ignore
            const newTriumphConfig = (new TriumphConfig()).load(this.triumphConfig?.jsonEncode());

            newTriumphConfig.id = null;
            GameConfigService.createTriumphConfig(newTriumphConfig).then((res: TriumphConfig | null) => {
                this.triumphConfig = res;
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
            if (this.triumphConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateTriumphConfig(this.triumphConfig)
                .then((res: TriumphConfig | null) => {
                    this.triumphConfig = res;
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
        const triumphConfigId = String(this.$route.params.triumphConfigId);
        GameConfigService.loadTriumphConfig(Number(triumphConfigId)).then((res: TriumphConfig | null) => {
            this.triumphConfig = res;
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
