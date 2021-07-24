<template>
    <div class="char-panel">
        <div class="char-sheet">
            <img class="avatar" :src="characterPortrait" alt="avatar">

            <div class="statuses">
                <Statuses :statuses="player.statuses" type="player" />
                <Statuses :statuses="player.diseases" type="disease" />
            </div>

            <div class="health-points">
                <div class="life">
                    <Tooltip>
                        <template #tooltip-trigger>
                            <ol style="align-items: center">
                                <li>
                                    <p><img src="@/assets/images/lp.png" alt="lp">{{ player.healthPoint.quantity }}</p>
                                </li>
                                <li>
                                    <ul>
                                        <li v-for="n in 14" :key="n" :class="isFull(n, player.healthPoint.quantity)" />
                                    </ul>
                                </li>
                            </ol>
                        </template>
                        <template #tooltip-content="{ formatContent }">
                            <h1 v-html="formatContent(player.healthPoint.name)" />
                            <p v-html="formatContent(player.healthPoint.description)" />
                        </template>
                    </Tooltip>
                </div>
                <div class="morale">
                    <Tooltip>
                        <template #tooltip-trigger>
                            <ol style="align-items: center">
                                <li>
                                    <p><img src="@/assets/images/moral.png" alt="mp">{{ player.moralPoint.quantity }}</p>
                                </li>
                                <li>
                                    <ul>
                                        <li v-for="n in 14" :key="n" :class="isFull(n, player.moralPoint.quantity)" />
                                    </ul>
                                </li>
                            </ol>
                        </template>
                        <template #tooltip-content="{ formatContent }">
                            <h1 v-html="formatContent(player.moralPoint.name)" />
                            <p v-html="formatContent(player.moralPoint.description)" />
                        </template>
                    </Tooltip>
                </div>
            </div>
            <div class="inventory">
                <inventory :items="player.items" :min-slot="3" @select="toggleItemSelection" />
            </div>
            <div v-if="! loading && target" class="interactions">
                <p v-if="selectedItem" class="item-name">
                    {{ selectedItem.name }}
                    <Statuses :statuses="selectedItem.statuses" type="item" />
                    <ActionButton
                        v-for="(action, key) in target.actions"
                        :key="key"
                        :action="action"
                        @click="executeTargetAction(target, action)"
                    />
                </p>
                <p v-else>
                    <ActionButton
                        v-for="(action, key) in target.actions"
                        :key="key"
                        :action="action"
                        @click="executeTargetAction(null, action)"
                    />
                </p>
            </div>
        </div>

        <div class="column">
            <ul class="skills">
                <!--        <li><img src="@/assets/images/skills/cook.png" alt="cook"></li>-->
                <!--        <li><img src="@/assets/images/skills/sturdy.png" alt="sturdy"></li>-->
                <!--        <li><img src="@/assets/images/skills/opportunist.png" alt="opportunist"></li>-->
                <!--        <li><img src="@/assets/images/skills/sturdy.png" alt="sturdy"></li>-->
            </ul>

            <div class="actions-sheet">
                <img src="@/assets/images/pam.png" alt="pam">
                <Tooltip>
                    <template #tooltip-trigger>
                        <div class="action-points">
                            <div class="actions">
                                <ul>
                                    <li v-for="n in 12" :key="n" :class="isFull(n, player.actionPoint.quantity)" />
                                </ul>
                            </div>
                            <div class="movements">
                                <ul>
                                    <li v-for="n in 12" :key="n" :class="isFull(n, player.movementPoint.quantity)" />
                                </ul>
                            </div>
                        </div>
                        <ul class="specials">
                            <!--          <li><img src="@/assets/images/pa_cook.png">x6</li>-->
                        </ul>
                    </template>
                    <template #tooltip-content="{ formatContent }">
                        <h1 v-html="formatContent(player.actionPoint.name)" />
                        <p v-html="formatContent(player.actionPoint.description)" />
                    </template>
                </Tooltip>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Player } from "@/entities/Player";
import { characterEnum } from '@/enums/character';
import Inventory from "@/components/Game/Inventory.vue";
import ActionButton from "@/components/Utils/ActionButton.vue";
import Statuses from "@/components/Utils/Statuses.vue";
import { mapActions, mapState } from "vuex";
import Tooltip from "@/components/Utils/ToolTip.vue";
import { Item } from "@/entities/Item";
import { Equipment } from "@/entities/Equipment";
import { Action } from "@/entities/Action";
import { Door } from "@/entities/Door";
import { defineComponent } from "vue";

interface CharPanelState {
    selectedItem: Item | Player | null
}

export default defineComponent ({
    name: "CharPanel",
    components: {
        ActionButton,
        Inventory,
        Statuses,
        Tooltip
    },
    props: {
        player: {
            type: Player,
            required: true
        }
    },
    data(): CharPanelState {
        return {
            selectedItem: null
        };
    },
    computed: {
        characterPortrait(): string {
            return characterEnum[this.player.character.key].portrait ?? '';
        },
        ...mapState('player', [
            'loading'
        ]),
        target(): Item | Player | null {
            return this.selectedItem || this.player;
        }
    },
    methods: {
        ...mapActions('action', [
            'executeAction'
        ]),
        isFull (value: number, threshold: number): Record<string, boolean> {
            return {
                "full": value <= threshold,
                'empty': value > threshold
            };
        },
        toggleItemSelection(item: Item): void {
            if (this.selectedItem === item) {
                this.selectedItem = null;
            } else {
                this.selectedItem = item;
            }
        },
        async executeTargetAction(target: Door | Item | Equipment | Player | null, action: Action): Promise<void> {
            await this.executeAction({ target, action });
            if (this.selectedItem instanceof Item && ! this.player.items.includes(this.selectedItem)) {
                this.selectedItem = null;
            }
        }
    }
});
</script>

<style lang="scss" scoped>

.char-panel {
    flex-direction: row;

    & .char-sheet {
        width: 176px;
        min-height: 459px;
        padding: 5px;
        border-top-left-radius: 4px;
        background: rgba(54, 76, 148, 0.35);

        & .avatar {
            width: 166px;
            height: auto;
        }

        .statuses {
            position: absolute;
            flex-flow: column wrap;
            align-items: center;
            margin: 2px;

            &::v-deep .status {
                margin-bottom: 3px;
            }
        }

        & .health-points {
            flex-direction: row;
            margin: 3px 0;

            & .life,
            & .morale {
                flex-direction: row;
                align-items: center;
                margin-right: 3px;

                & p,
                & ul {
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    border: 1px solid #4077b5;
                    border-radius: 3px;
                    background: rgba(58, 106, 171, 1);
                    box-shadow: 0 0 4px 1px inset rgba(28, 29, 56, 1);
                }

                & p {
                    min-width: 24px;
                    height: 15px;
                    margin: 1px;
                    padding-right: 1px;
                    font-size: 0.5em;
                    letter-spacing: 0.03em;
                    border-right-width: 0;

                    & img {
                        width: 11px;
                        height: 13px;
                        margin-right: 1px;
                    }
                }

                & ul {
                    /* min-width: 59px; */
                    height: 11px;
                    border-radius: 2px;
                    margin-left: -2px;
                    border-left-width: 0;

                    & li {
                        width: 3px;
                        height: 5px;
                        margin-right: 1px;
                        background: rgba(138, 170, 44, 1);
                        box-shadow: 1px 1px 0 0 inset rgba(255, 255, 255, 0.7);

                        &.empty {
                            background: rgba(37, 72, 137, 1);
                            box-shadow: 1px 1px 0 0 inset rgba(78, 154, 255, 0.7);
                        }
                    }
                }
            }
        }

        & .inventory ul {
            display: flex;
            flex-direction: row;

            & li {
                @include inventory-slot();
            }
        }

        & .interactions {
            margin-top: 12px;

            .item-name {
                margin: 0 0 4px 0;
                font-size: 0.83em;
                letter-spacing: 0.03em;
                font-variant: small-caps;

                &::v-deep .status {
                    vertical-align: middle;
                    margin-left: 2px;
                }
            }
        }
    }

    & .column {
        justify-content: space-between;

        & .skills {
            display: flex;
            flex-direction: column;
            min-width: 32px;
            float: right;

            & li {
                display: flex;
                align-items: center;

                /* justify-content: center; */
                width: 30px;
                height: 34px;
                padding-right: 3px;
                margin-bottom: 7px;
                background: transparent url('~@/assets/images/skills/skillblock_gold.png') center left no-repeat;
                border-left: 1px solid #191a53;

                &:nth-child(1) {
                    background: transparent url('~@/assets/images/skills/skillblock.png') center left no-repeat;
                }

                &:nth-child(2) {
                    background: transparent url('~@/assets/images/skills/skillblock_once.png') center left no-repeat;
                }
            }
        }

        & .actions-sheet {
            align-items: center;
            justify-content: flex-start;
            width: 28px;
            min-height: 134px;
            padding: 5px 5px 5px 0;
            border-top-right-radius: 4px;
            background: rgba(54, 76, 148, 0.35);

            & > img { margin: 3px; }

            & .action-points {
                flex-direction: row;

                & > div {
                    & ul {
                        display: block;
                        flex-direction: column;
                        align-items: center;
                        border: 3px solid transparent;
                        border-image: url('~@/assets/images/actionpoints_bg.svg') 40% stretch;

                        & li {
                            width: 5px;
                            height: 6px;
                            border-bottom: 1px solid black;
                            background: rgba(138, 170, 44, 1);
                            box-shadow: 0 -1px 0 0 inset rgba(0, 0, 0, 0.4);
                        }
                    }
                }

                & .movements ul li {
                    background: rgb(0, 255, 228);
                    background: linear-gradient(135deg, rgba(255, 255, 255, 1) 5%, rgba(0, 255, 228, 1) 20%);

                    &.empty {
                        background: rgb(14, 62, 56);
                        background: linear-gradient(135deg, rgba(18, 85, 106, 1) 5%, rgba(14, 62, 56, 1) 20%);
                    }
                }

                & .actions ul li {
                    background: rgb(255, 85, 153);
                    background: linear-gradient(135deg, rgba(255, 255, 255, 1) 5%, rgba(255, 85, 153, 1) 20%);

                    &.empty {
                        background: rgb(64, 0, 0);
                        background: linear-gradient(135deg, rgba(77, 17, 32, 1) 5%, rgba(64, 0, 0, 1) 20%);
                    }
                }
            }

            & .specials li {
                display: flex;
                flex-direction: row;
                align-items: baseline;
                margin: 2px 0;
                font-size: 0.65em;
                font-weight: 700;

                & img { margin-right: -3px; }
            }
        }
    }
}

</style>
