<template>
    <div class="char-panel" :class="player.character.key">
        <div class="char-sheet">
            <div class="char-card">
                <div class="avatar">
                    <img
                        :src="characterPortrait"
                        alt="avatar"
                        @mousedown.stop="toggleItemSelection(null)"
                    >
                </div>

                <ul class="statuses">
                    <Statuses :statuses="player.statuses" type="player" />
                    <Statuses :statuses="player.diseases" type="disease" />
                </ul>

                <div class="health-points">
                    <div class="life">
                        <Tippy tag="ol">
                            <li class="quantityLife">
                                <ul>
                                    <li v-for="n in player?.healthPoint?.max" :key="n" :class="isFull(n, player.healthPoint.quantity)" />
                                </ul>
                            </li>
                            <li class="iconLife">
                                <p><img :src="getImgUrl('lp.png')" alt="lp">{{ player.healthPoint.quantity }}</p>
                            </li>
                            <template #content>
                                <h1 v-html="formatContent(player.healthPoint.name)" />
                                <p v-html="formatContent(player.healthPoint.description)" />
                            </template>
                        </Tippy>
                    </div>
                    <div class="morale">
                        <Tippy tag="ol">
                            <li class="quantityMorale">
                                <ul>
                                    <li v-for="n in player?.moralPoint?.max" :key="n" :class="isFull(n, player.moralPoint.quantity)" />
                                </ul>
                            </li>
                            <li class="iconMorale">
                                <p><img :src="getImgUrl('moral.png')" alt="mp">{{ player.moralPoint.quantity }}</p>
                            </li>
                            <template #content>
                                <h1 v-html="formatContent(player.moralPoint.name)" />
                                <p v-html="formatContent(player.moralPoint.description)" />
                            </template>
                        </Tippy>
                    </div>
                </div>
            </div>
            <div class="inventory">
                <inventory
                    :items="player.items"
                    :min-slot="3"
                    :selected-item="getTargetItem"
                    @select="toggleItemSelection"
                />
            </div>
            <div v-if="! loading && target" class="interactions">
                <div v-if="selectedItem" class="item">
                    <span class="item-name">
                        {{ selectedItem.name }}
                        <Statuses :statuses="selectedItem.statuses" type="item" />
                    </span>
                    <ActionButton
                        v-for="(action, key) in target.actions"
                        :key="key"
                        :action="action"
                        @click="executeTargetAction(target, action)"
                    />
                </div>
                <div v-else>
                    <ActionButton
                        v-for="(action, key) in target.actions"
                        :key="key"
                        :action="action"
                        @click="executeTargetAction(null, action)"
                    />
                </div>
            </div>
        </div>

        <div class="column">
            <div class="skills">
                <ul>
                    <Tippy
                        tag="li"
                        v-for="skill, index in skillsToDisplay"
                        :key="skill.key"
                        :class="skillSlotClass(index + 1)"
                    >
                        <img class="skill-image" :src="skillImage(skill)" :alt="skill.name">
                        <template #content>
                            <h1 v-html="formatText(skill.name)" />
                            <p v-html="formatText(skill.description)" />
                        </template>
                    </Tippy>
                </ul>
                <ul>
                    <Tippy
                        tag="li"
                        v-for="index in selectableSkillsToDisplay.length"
                        :key="index"
                        :class="skillSlotClass(index + skillsToDisplay.length)"
                    >
                        <button class="flashing" @click="openSkillSelectionPopUp">
                            <img :src="skillSlotImage(index + skillsToDisplay.length)" alt="Plus">
                        </button>
                        <template #content>
                            <h1 v-html="formatText($t('charPanel.availableSlot'))" />
                            <p v-html="formatText($t('charPanel.chooseNewSkill', { character: player.character.name }))" />
                        </template>
                    </Tippy>
                </ul>
                <Tippy
                    tag="li"
                    class="genome"
                    v-if="player.isMush()"
                    @click="toggleMushSkillsDisplay">
                    <button>
                        <img :src="getImgUrl('mush_module.png')" alt="Mush Genome">
                    </button>
                    <template #content>
                        <h1 v-html="formatText($t('charPanel.mushGenome'))" />
                        <p v-html="formatText($t('charPanel.displayYourHumanSkills'))" v-if="displayMushSkills" />
                        <p v-html="formatText($t('charPanel.displayYourMushSkills'))" v-else />
                    </template>
                </Tippy>
            </div>

            <div class="actions-sheet">
                <img :src="getImgUrl('pam.png')" alt="pam">
                <Tippy tag="div">
                    <div class="action-points">
                        <div class="actions">
                            <ul>
                                <li v-for="n in player?.actionPoint?.max" :key="n" :class="isFull(n, player.actionPoint.quantity)" />
                            </ul>
                        </div>
                        <div class="movements">
                            <ul>
                                <li v-for="n in player?.movementPoint?.max" :key="n" :class="isFull(n, player.movementPoint.quantity)" />
                            </ul>
                        </div>
                    </div>
                    <template #content>
                        <h1 v-html="formatContent(player.actionPoint.name)" />
                        <p v-html="formatContent(player.actionPoint.description)" />
                    </template>
                </Tippy>
                <ul class="specials">
                    <Tippy
                        tag="li"
                        v-for="(point) in getRelevantSkillPoints"
                        :key="point.key"
                        class="skillPoint"
                    >
                        <img :src="skillPointImg(point)" :alt="point.key">x{{ point.charge.quantity }}
                        <template #content>
                            <h1 v-html="formatContent(point.charge.name)" />
                            <p v-html="formatContent(point.charge.description)" />
                        </template>
                    </Tippy>
                </ul>
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
import { Item } from "@/entities/Item";
import { Equipment } from "@/entities/Equipment";
import { Action } from "@/entities/Action";
import { Door } from "@/entities/Door";
import { defineComponent } from "vue";
import { StatusPlayerNameEnum } from "@/enums/status.player.enum";
import { formatText } from "@/utils/formatText";
import { getImgUrl } from "@/utils/getImgUrl";
import { SkillPoint } from "@/entities/SkillPoint";
import { skillPointEnum } from "@/enums/skill.point.enum";
import { SkillIconRecord } from "@/enums/skill.enum";

type Skill = {
    key: string;
    name: string;
    description: string;
}

export default defineComponent ({
    name: "CharPanel",
    components: {
        ActionButton,
        Inventory,
        Statuses
    },
    props: {
        player: {
            type: Player,
            required: true
        }
    },
    computed: {
        ...mapState('player', ['loading', 'selectedItem']),
        characterPortrait(): string {
            return characterEnum[this.player.character.key].portrait ?? '';
        },
        getTargetItem(): Item | null {
            return this.selectedItem;
        },
        getRelevantSkillPoints(): Array<SkillPoint> {
            return this.player.skillPoints.filter(player => player.charge.quantity > 0);
        },
        skillsToDisplay(): Array<Skill> {
            return this.displayMushSkills ? this.player.mushSkills : this.player.humanSkills;
        },
        selectableSkillsToDisplay(): Array<Skill> {
            return this.displayMushSkills ? this.player.character.selectableMushSkills : this.player.character.selectableHumanSkills;
        },
        target(): Item | Player | null {
            return this.selectedItem || this.player;
        }
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
            'selectTarget': 'player/selectTarget',
            'openSkillSelectionPopUp': 'popup/openSkillSelectionPopUp'
        }),
        getImgUrl,
        formatText,
        isFull (value: number, threshold: number): Record<string, boolean> {
            return {
                "full": value <= threshold,
                'empty': value > threshold
            };
        },
        skillSlotClass(index: number): string {
            switch (index) {
            case 1:
                return 'skill-slot-basic';
            case 2:
                return 'skill-slot-once';
            default:
                return 'skill-slot-gold';
            }
        },
        skillSlotImage(index: number): string {
            switch (index) {
            case 1:
                return getImgUrl('skills/basicplus.png');
            case 2:
                return getImgUrl('skills/onceplus.png');
            default:
                return getImgUrl('skills/goldplus.png');
            }
        },
        skillImage(skill: Skill): string {
            return SkillIconRecord[skill.key].icon ?? '';
        },
        skillPointImg(point: SkillPoint): string {
            return skillPointEnum[point.key].icon ?? '';
        },
        toggleItemSelection(item: Item | null): void {
            if (this.selectedItem === item) {
                this.selectTarget({ target: null });
            } else {
                this.selectTarget({ target: item });
            }
        },
        toggleMushSkillsDisplay(): void {
            this.displayMushSkills = !this.displayMushSkills;
        },
        async executeTargetAction(target: Door | Item | Equipment | Player | null, action: Action): Promise<void> {
            if(action.canExecute) {
                await this.executeAction({ target, action });
                if (this.selectedItem instanceof Item && ! this.player.items.includes(this.selectedItem)) {
                    this.selectedItem = null;
                }
            }
        }
    },
    data() {
        return {
            StatusPlayerNameEnum,
            displayMushSkills: this.player.isMush()
        };
    }
});
</script>

<style lang="scss" scoped>

.iconLife, .iconMorale {
    position: relative;
}

.char-panel {
    flex-direction: row;

    .char-sheet {
        max-width: 176px;
        min-height: 459px;
        padding: 5px;
        border-top-left-radius: 4px;
        background: rgba(54, 76, 148, 0.35);

        .avatar img {
            width: 100%;
            max-width: 166px;
            height: auto;
        }

        .statuses {
            position: absolute;
            flex-flow: column wrap;
            align-items: flex-start;
            margin: 2px;
            max-height: 215px;
            gap: 3px;
        }
    }
}


.health-points {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: space-evenly;
    row-gap: 0.6em;
    margin: -.75em 0 .25em;

    .life,
    .morale {
        flex-direction: row;
        align-items: center;
        filter: drop-shadow(0 0 5px $deepBlue);


        ol {
            align-items: center;
            flex-direction: column-reverse;

            @media screen and (max-width: $breakpoint-desktop-l) { flex-direction: row-reverse; }

            li:first-child { z-index: 1; }
        }

        p,
        ul {
            display: flex;
            flex-direction: row;
            align-items: center;
            border: 1px solid lighten($greyBlue, 3.2);
            border-radius: 3px;
            background: $greyBlue;
            box-shadow: 0 0 4px 1px inset rgba(28, 29, 56, 1);
        }

        p {
            margin: 0 0 -1px 0;
            padding: .15em .4em .2em;
            font-size: 0.8em;
            letter-spacing: 0.03em;
            border-bottom-width: 0;
            text-shadow: 0 0 2px black, 0 0 2px black;

            img {
                width: 11px;
                height: 13px;
                margin-right: 1px;
            }
        }

        ul {
            padding: .1em .2em;
            border-radius: 2px;

            @media screen and (max-width: $breakpoint-desktop-m) { display: none; }

            li {
                width: 4px;
                height: 5px;
                background: rgba(138, 170, 44, 1);
                box-shadow: 1px 1px 0 0 inset rgba(255, 255, 255, 0.7);

                &:not(:last-child) { margin-right: 1px; }

                &.empty {
                    background: rgba(37, 72, 137, 1);
                    box-shadow: 1px 1px 0 0 inset rgba(78, 154, 255, 0.7);
                }
            }
        }
    }
}

div.inventory {
    overflow: visible;
    margin: 0 -1px;

    @media screen and (max-width: $breakpoint-desktop-l) {
        width: 110px;
        margin: 0 auto;
    }

    @media screen and (max-width: $breakpoint-desktop-m) { width: 82px; }
}

.interactions {
    margin-top: 12px;

    .item {
        margin: 0 0 4px 0;
        letter-spacing: 0.03em;
        font-variant: small-caps;

        &::v-deep(.status) {
            vertical-align: middle;
            margin-left: 2px;
        }
    }
}

.column {
    justify-content: space-between;
}

.skills {
    ul {
        display: flex;
        flex-direction: column;
        float: right;
        min-width: 32px;
    }

    li {
        display: flex;
        position: relative;
        align-items: center;

        /* justify-content: center; */
        width: 30px;
        height: 34px;
        padding-right: 3px;
        margin-bottom: 7px;
        background: transparent url('/src/assets/images/skills/skillblock.png') center left no-repeat;
        border-left: 1px solid #191a53;

        button {
            @include button-style();
            width: 22px;
            height: 22px;
            padding: 0;

            img {
                top: 0;
                padding: 0;
            }
        }

        &.skill-slot {

            &:before {
                position: absolute;
                z-index: 1;
                top: 14px;
                left: 13px;
                width: 20px;
                height: 23px;
                padding-top: 7px;
                font-family: $font-days-one;
                font-size: .9em;
                text-align: center;
            }
        }

        &.skill-slot-basic {
            @extend .skill-slot;
            background: transparent url('/src/assets/images/skills/skillblock.png') center left no-repeat;
        }

        &.skill-slot-once {
            @extend .skill-slot;
            background: transparent url('/src/assets/images/skills/skillblock_once.png') center left no-repeat;
        }

        &.skill-slot-gold {
            @extend .skill-slot;
            background: transparent url('/src/assets/images/skills/skillblock_gold.png') center left no-repeat;
        }

        &.locked {
            background: transparent url('/src/assets/images/skills/skillblock_gold.png') center left no-repeat;

            &:before {
                content: "";
                position: absolute;
                z-index: 1;
                top: 14px;
                left: 13px;
                background: transparent url('/src/assets/images/skills/lock_gold.png') center no-repeat;
                width: 20px;
                height: 23px;
                padding-top: 7px;
                font-family: $font-days-one;
                font-size: .9em;
                text-align: center;
            }
        }

        &:nth-child(2).locked:before { content:"2"; }
        &:nth-child(3).locked:before { content:"3"; }
        &:nth-child(4).locked:before { content:"4"; }
    }
}

.actions-sheet {
    align-items: center;
    justify-content: flex-start;
    width: 28px;
    min-height: 134px;
    padding: 5px 5px 5px 0;
    border-top-right-radius: 4px;
    background: rgba(54, 76, 148, 0.35);

    & > img { margin: 3px; }

    .action-points {
        flex-direction: row;

        & > div {
            ul {
                display: block;
                flex-direction: column;
                align-items: center;
                border: 3px solid transparent;
                border-image: url('/src/assets/images/actionpoints_bg.svg') 40% stretch;

                li {
                    width: 5px;
                    height: 6px;
                    border-bottom: 1px solid black;
                    background: rgba(138, 170, 44, 1);
                    box-shadow: 0 -1px 0 0 inset rgba(0, 0, 0, 0.4);
                }
            }
        }

        .movements ul li {
            background: rgb(0, 255, 228);
            background: linear-gradient(135deg, rgba(255, 255, 255, 1) 5%, rgba(0, 255, 228, 1) 20%);

            &.empty {
                background: rgb(14, 62, 56);
                background: linear-gradient(135deg, rgba(18, 85, 106, 1) 5%, rgba(14, 62, 56, 1) 20%);
            }
        }

        .actions ul li {
            background: rgb(255, 85, 153);
            background: linear-gradient(135deg, rgba(255, 255, 255, 1) 5%, rgba(255, 85, 153, 1) 20%);

            &.empty {
                background: rgb(64, 0, 0);
                background: linear-gradient(135deg, rgba(77, 17, 32, 1) 5%, rgba(64, 0, 0, 1) 20%);
            }
        }
    }
    .specials {
        display: flex;
        flex-direction: column;

        li {
            display: flex;
            flex-direction: row;
            align-items: baseline;
            margin: 2px 0;
            font-size: 0.75em;
            font-weight: 700;

            img { margin-right: -3px; }
        }
    }
}

@media screen and (max-width: $breakpoint-desktop-m) and (orientation: portrait) {
    .char-panel {
        width: 100%;

        .char-sheet {
            display: block;
            width: 100%;
            // flex-direction: row;
            // align-items: flex-start;
            max-width: initial;
            min-height: initial;

            .avatar {
            align-items: center;
            justify-content: center;
            width: 110px;
            height: 70px;
            overflow: hidden;

                img { width: initial; }
            }

            .char-card { float: left; }

            .health-points { margin-top: -0.3em; }


            .inventory {
                float: left;
                clear: left;
                width: 110px;
            }

            .interactions {
                padding-left: 8%;
                padding-right: 8%;
                margin: auto;
            }
        }
    }

    @each $crewmate, $face-position-x, $face-position-y in $face-position { // adjust the image position in the crewmate avatar div
        $translate-x : (50% - $face-position-x);
        $translate-y : (50% - $face-position-y);
        .#{$crewmate} .avatar img {
            transform: translate($translate-x, $translate-y);
        }
    }

}


</style>
