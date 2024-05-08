<template>
    <Tippy tag="div" :class="['action-button-flex-v2']" v-if=action>
        <a
            :class="['action-button', cssClass, isDisabled].join(' ')"
            href="#">
            <span v-if="action.movementPointCost > 0 && !action.actionPointCost" class="cost">{{ action.movementPointCost }}<img :src="getImgUrl('pm.png')" alt="mp"></span>
            <span v-else-if="action.actionPointCost > 0 && !action.movementPointCost" class="cost">{{ action.actionPointCost }}<img :src="getImgUrl('pa.png')" alt="ap"></span>
            <span v-else-if="action.actionPointCost > 0 && action.movementPointCost > 0" class="cost">{{ action.actionPointCost }}<img :src="getImgUrl('pa.png')" alt="ap">{{ action.movementPointCost }}<img :src="getImgUrl('pm.png')" alt="mp"></span>
            <!-- @TODO -> Handle multiple specialist cost action, if that's the case display the most prioritized point -->
            <span v-if="action.specialistPointCosts.length > 0" class="cost">
                1<img :src="specialistPointImage(action)" alt="{{ action.specialistPointCosts[0] }}">
            </span>
            <span v-if="action.canExecute">{{ action.name }}</span>
            <span v-else><s>{{ action.name }}</s></span>
            <span v-if="action.successRate < 100" class="success-rate"> ({{ action.successRate }}%)</span>
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
import { SpecialistPointEnum, specialistPointEnum } from "@/enums/specialistPoint.enum";
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
        specialistPointImage(action: Action): string {
            return specialistPointEnum[action.specialistPointCosts[0]].icon ?? '';
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
