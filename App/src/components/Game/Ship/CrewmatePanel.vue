<template>
    <div class="crewmate-container" :class="target.character.key">
        <div class="mate">
            <div class="card">
                <div class="avatar">
                    <img :src="portrait" alt="crewmate">
                </div>
                <div>
                    <p class="name">
                        {{ target.character.name }}
                    </p>
                    <div class="statuses">
                        <Statuses :statuses="target.statuses" type="player" />
                    </div>
                </div>
            </div>
            <p class="presentation">
                Description (to be implemented)
            </p>
            <div class="skills">
                Skills (to be implemented)
            </div>
        </div>
        <div class="interactions">
            <ActionButton
                v-for="(action, key) in target.actions"
                :key="key"
                :action="action"
                @click="() => $emit('executeAction', action)"
            />
        </div>
    </div>
</template>

<script lang="ts">
import ActionButton from "@/components/Utils/ActionButton.vue";
import Statuses from "@/components/Utils/Statuses.vue";
import { Player } from "@/entities/Player";
import { characterEnum } from '@/enums/character';
import { defineComponent } from "vue";


export default defineComponent ({
    name: "CrewmatePanel",
    components: {
        ActionButton,
        Statuses
    },
    props: {
        target: {
            type: Player,
            required: true
        }
    },
    emits: [
        'executeAction'
    ],
    computed: {
        portrait(): string {
            return characterEnum[this.target.character.key].portrait ?? '';
        }
    }
});
</script>

<style lang="scss" scoped>
.crewmate-container {
    position: absolute;
    z-index: 5;
    bottom: 0;
    width: calc(100% - 16px);
    flex-direction: row;
    padding: 3px;
    background-color: #222a6b;
}

.mate {
    flex: 1;
    max-width: 50%;
    border-right: 1px dotted #4a5d8f;
    padding: 1px;
    padding-right: 4px;
}

.card {
    flex-flow: row wrap;

    .avatar {
        align-items: center;
        justify-content: center;
        width: 110px;
        height: 70px;
        overflow: hidden;
        border: 1px solid #161951;

        img {
            position: relative;
            width: 210px;
            height: auto;
        }
    }

    .statuses {
        flex-direction: row;
        flex-wrap: wrap;
        font-size: 0.9em;

        &::v-deep .status {
            padding: 1px;
        }
    }

    .name {
        font-weight: 700;
        text-transform: uppercase;
        padding-left: 4px;
        margin: 0;
    }
}

.presentation {
    margin: 0;
    padding: 2px 0;
    font-size: 0.9em;
    font-style: italic;
}

.skills {
    flex-direction: row;
    flex-wrap: wrap;
}

.interactions {
    flex: 1;
    max-width: 50%;
    padding: 1px;
    padding-left: 4px;
}

@each $crewmate, $face-position-x, $face-position-y in $face-position { // adjust the image position in the crewmate avatar div
    $translate-x : (50% - $face-position-x);
    $translate-y : (50% - $face-position-y);
    .#{$crewmate} .avatar img {
        transform: translate($translate-x, $translate-y);
    }
}


</style>
