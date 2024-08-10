<template>
    <Tippy tag="div" :class="['action-button-flex-v2']" v-if=action>
        <a
            :class="['action-button', cssClass, isDisabled].join(' ')"
            href="#">
            <span v-if="action.actionPointCost && !action.movementPointCost && !action.moralePointCost" class="cost">{{ action.actionPointCost }}<img :src="getImgUrl('pa.png')" alt="ap"></span>
            <span v-else-if="action.movementPointCost && !action.actionPointCost && !action.moralePointCost" class="cost">{{ action.movementPointCost }}<img :src="getImgUrl('pm.png')" alt="mp"></span>
            <span v-else-if="action.moralePointCost && !action.actionPointCost && !action.movementPointCost" class="cost">{{ action.moralePointCost }}<img :src="getImgUrl('moral.png')" alt="pmo" /></span>

            <span v-else-if="action.actionPointCost && action.movementPointCost && !action.moralePointCost" class="cost">{{ action.actionPointCost }}<img :src="getImgUrl('pa.png')" alt="ap">{{ action.movementPointCost }}<img :src="getImgUrl('pm.png')" alt="mp"></span>
            <span v-else-if="action.actionPointCost && action.moralePointCost && !action.movementPointCost" class="cost">{{ action.actionPointCost }}<img :src="getImgUrl('pa.png')" alt="ap">{{ action.moralePointCost }}<img :src="getImgUrl('moral.png')" alt="pmo"></span>
            <span v-else-if="action.movementPointCost && action.moralePointCost && !action.actionPointCost" class="cost">{{ action.movementPointCost }}<img :src="getImgUrl('pm.png')" alt="mp">{{ action.moralePointCost }}<img :src="getImgUrl('moral.png')" alt="pmo"></span>

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
        </a>
        <template #content>
            <h1 v-html="formatContent(action.name)" />
            <p v-html="formatContent(action.description)" />
            <!--<p v-else v-html="formatContent(action_fail.description)"> </p>-->
        </template>
    </Tippy>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { Action } from "@/entities/Action";
import { Player } from "@/entities/Player";
import { StatusPlayerNameEnum } from "@/enums/status.player.enum";
import { getImgUrl } from "@/utils/getImgUrl";
import { skillPointEnum } from "@/enums/skill.point.enum";
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
    @include button-style();
    margin: 0.2rem !important;
    flex-flow: row wrap;
}
.medium {
    min-width: 10em;
}
.wide {
    min-width: 15em;
}
</style>
