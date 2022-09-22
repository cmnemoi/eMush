<template>
    <div v-if="actionCost" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.actionCost.actionPointCost')"
                id="actionCost_actionPointCost"
                v-model="actionCost.actionPointCost"
                type="number"
                :errors="errors.actionPointCost"
            ></Input>
            <Input
                :label="$t('admin.actionCost.movementPointCost')"
                id="actionCost_movementPointCost"
                v-model="actionCost.movementPointCost"
                type="number"
                :errors="errors.movementPointCost"
            ></Input>
            <Input
                :label="$t('admin.actionCost.moralPointCost')"
                id="actionCost_value"
                v-model="actionCost.moralPointCost"
                type="number"
                :errors="errors.moralPointCost"
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
import Input from "@/components/Utils/Input.vue";
import { ActionCost } from "@/entities/Config/ActionCost";

interface ActionCostState {
    actionCost: null|ActionCost
    errors: any
}

export default defineComponent({
    name: "ActionCostDetailPage",
    components: {
        Input
    },
    data: function (): ActionCostState {
        return {
            actionCost: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.actionCost === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateActionCost(this.actionCost)
                .then((res: ActionCost | null) => {
                    this.actionCost = res;
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
        const actionCostId = Number(this.$route.params.actionCostId);
        GameConfigService.loadActionCost(actionCostId).then((res: ActionCost | null) => {
            this.actionCost = res;
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
