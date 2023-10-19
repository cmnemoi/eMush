<template>
    <div v-if="eventConfig" class="center">
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.eventConfig.name')"
                id="eventConfig_name"
                v-model="eventConfig.name"
                type="text"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.eventConfig.eventName')"
                id="eventConfig_eventName"
                v-model="eventConfig.eventName"
                type="text"
                :errors="errors.eventName"
            />
            <Input
                :label="$t('admin.eventConfig.quantity')"
                id="eventConfig_quantity"
                v-model="eventConfig.quantity"
                type="text"
                :errors="errors.quantity"
            />


            <Input
                :label="$t('admin.eventConfig.targetVariable')"
                id="eventConfig_targetVariable"
                v-model="eventConfig.targetVariable"
                type="text"
                :errors="errors.targetVariable"
            />
            <Input
                :label="$t('admin.eventConfig.variableHolderClass')"
                id="eventConfig_variableHolderClass"
                v-model="eventConfig.variableHolderClass"
                type="text"
                :errors="errors.variableHolderClass"
            />
        </div>
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import urlJoin from "url-join";
import Input from "@/components/Utils/Input.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import { EventConfig } from "@/entities/Config/EventConfig";

interface EventConfigState {
    eventConfig: null|EventConfig
    errors: any
}

export default defineComponent({
    name: "VariableEventConfigState",
    components: {
        Input,
        UpdateConfigButtons
    },
    data: function (): EventConfigState {
        return {
            eventConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if (this.eventConfig === null) return;

            const newEventConfig = this.eventConfig;
            newEventConfig.id = null;

            GameConfigService.createEventConfig(newEventConfig)
                .then((res: EventConfig | null) => {
                    const newEventConfigUrl = urlJoin(process.env.VUE_APP_URL + '/config/event-config', String(res?.id));
                    window.location.href = newEventConfigUrl;
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
            if (this.eventConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateEventConfig(this.eventConfig)
                .then((res: EventConfig | null) => {
                    this.eventConfig = res;
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
    },
    beforeMount() {
        const eventConfigId = String(this.$route.params.configId);
        GameConfigService.loadEventConfig(Number(eventConfigId)).then((res: EventConfig | null) => {
            if (res instanceof EventConfig) {
                this.eventConfig = res;
            }
        });

    }
});
</script>


<style lang="scss" scoped>
   .configCheckbox {
       margin-left: 10px;
       margin-right: 10px;
   }
</style>
