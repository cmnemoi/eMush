<template>
    <Tippy tag="div" :class="['action-button-flex-v2']" v-if="action">
        <button :class="['action-button', cssClass, isDisabled].join(' ')">
            <span v-if="action.actionPointCost && !action.movementPointCost && !action.moralePointCost" class="cost">{{ action.actionPointCost }}<img :src="getImgUrl('ui_icons/player_variables/pa.png')" alt="ap"></span>
            <span v-else-if="action.movementPointCost && !action.actionPointCost && !action.moralePointCost" class="cost">{{ action.movementPointCost }}<img :src="getImgUrl('ui_icons/player_variables/pm.png')" alt="mp"></span>
            <span v-else-if="action.moralePointCost && !action.actionPointCost && !action.movementPointCost" class="cost">{{ action.moralePointCost }}<img :src="getImgUrl('ui_icons/player_variables/moral.png')" alt="pmo" /></span>

            <span v-else-if="action.actionPointCost && action.movementPointCost && !action.moralePointCost" class="cost">{{ action.actionPointCost }}<img :src="getImgUrl('ui_icons/player_variables/pa.png')" alt="ap">{{ action.movementPointCost }}<img :src="getImgUrl('ui_icons/player_variables/pm.png')" alt="mp"></span>
            <span v-else-if="action.actionPointCost && action.moralePointCost && !action.movementPointCost" class="cost">{{ action.actionPointCost }}<img :src="getImgUrl('ui_icons/player_variables/pa.png')" alt="ap">{{ action.moralePointCost }}<img :src="getImgUrl('ui_icons/player_variables/moral.png')" alt="pmo"></span>
            <span v-else-if="action.movementPointCost && action.moralePointCost && !action.actionPointCost" class="cost">{{ action.movementPointCost }}<img :src="getImgUrl('ui_icons/player_variables/pm.png')" alt="mp">{{ action.moralePointCost }}<img :src="getImgUrl('ui_icons/player_variables/moral.png')" alt="pmo"></span>

            <!-- @TODO -> Handle multiple specialist cost action, if that's the case display the most prioritized point -->
            <span v-if="action.skillPointCosts.length > 0" class="cost">
                1<img :src="skillPointImage(action)" :alt="action.skillPointCosts[0]">
            </span>
            <span v-if="action.canExecute">
                {{ action.name }}
                <span v-if="action.successRate < 100" class="success-rate"> ({{ action.successRate }}%)</span>
            </span>
            <span v-else>
                <s>{{ action.name }}</s>
                <span v-if="action.successRate < 100" class="success-rate"> ({{ action.successRate }}%)</span>
            </span>
        </button>
        <template #content>
            <h1 v-html="formatContent(action.name)" />
            <p v-html="formatContent(action.description)" />
        </template>
    </Tippy>
</template>

<script lang="ts">
import { Action } from "@/entities/Action";
import { Player } from "@/entities/Player";
import { skillPointEnum } from "@/enums/skill.point.enum";
import { StatusPlayerNameEnum } from "@/enums/status.player.enum";
import { getImgUrl } from "@/utils/getImgUrl";
import { defineComponent } from "vue";
import { Tippy } from "vue-tippy";

export default defineComponent ({
    components: { Tippy },
    props: {
        action: Action,
        cssClass: String,
        player: Player
    },
    computed:{
        isDisabled(): string {
            return !this.action?.canExecute ? "disabled" : "";
        }
    },
    methods: {
        getImgUrl,
        skillPointImage(action: Action): string {
            return skillPointEnum[action.skillPointCosts[0]].icon ?? '';
        }
    },
    data() {
        return {
            StatusPlayerNameEnum
        };
    }
});
</script>

<style lang="scss" scoped>
.action-button {
    margin: 0.2rem !important;
    flex-flow: row wrap;
    @include button-style();
}
.medium {
    min-width: 10em;
}
.wide {
    min-width: 15em;
}

@media screen and (max-width: $breakpoint-desktop-m) {
    .action-button-flex-v2 {
        margin-bottom: 16px;
        position: relative;

        .action-button {
            padding: 8px 1rem;
            position: relative;

            &::before {
                content: '';
                position: absolute;
                top: -10px;
                bottom: -10px;
                left: 0;
                right: 0;
                z-index: -1;
            }
        }
    }
}
</style>
