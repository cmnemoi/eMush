<template>
    <div class="crewmate-container">
        <div class="mate">
            <div class="card">
                <div class="avatar">
                    <img :src="portrait" alt="crewmate">
                </div>
                <div>
                    <p class="name">
                        {{ target.characterValue }}
                    </p>
                    <div class="status">
                        <span v-for="(status, id) in target.statuses" :key="id">
                            <img :src="playerStatusIcon(status)">
                        </span>
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
                @click="executeTargetAction(action)"
            />
        </div>
    </div>
</template>

<script>
import { mapActions } from "vuex";
import ActionButton from "@/components/Utils/ActionButton";
import ActionService from "@/services/action.service";
import { Player } from "@/entities/Player";
import { characterEnum } from '@/enums/character';
import { statusPlayerEnum } from "@/enums/status.player.enum";


export default {
    name: "CrewmatePanel",
    components: {
        ActionButton
    },
    props: {
        target: Player
    },
    computed: {
        portrait() {
            return characterEnum[this.target.characterKey].portrait;
        },
        playerStatusIcon() {
            return (status) => {
                const statusImages = statusPlayerEnum[status.key];
                return typeof statusImages !== 'undefined' ? statusImages.icon : null;
            };
        }
    },
    methods: {
        ...mapActions('player', [
            'reloadPlayer',
            'setLoading'
        ]),
        async executeTargetAction(action) {
            this.setLoading();
            await ActionService.executeTargetAction(this.target, action);
            await this.reloadPlayer();
        }
    }
};
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

    .mate {
        flex: 1;
        max-width: 50%;
        border-right: 1px dotted #4a5d8f;
        padding: 1px;
        padding-right: 4px;
        font-size: 0.85em;

        .card {
            flex-flow: row wrap;

            & > * { flex: 1; } //divs will wrap only if too small

            .avatar {
                align-items: center;
                min-width: 110px;
                height: 70px;
                overflow: hidden;
                border: 1px solid #161951;

                img {
                    position: relative;
                    width: 210px;
                    height: auto;
                    left: 20px;
                    top: -36px;
                }
            }

            .status {
                flex-direction: row;
                flex-wrap: wrap;
                font-size: 0.9em;
                span { padding: 1px; }
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
    }

    .interactions {
        flex: 1;
        max-width: 50%;
        padding: 1px;
        padding-left: 4px;
    }
}
</style>
