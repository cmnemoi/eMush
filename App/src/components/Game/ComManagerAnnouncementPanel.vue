<template>
    <div class="terminal">
        <h1><img :src="getImgUrl('spot2.svg')">{{ $t('game.ComManagerAnnouncementPanel.title') }}</h1>
        <div class="comm-manager-announcement-terminal-container">
            <TerminalTips :content="$t('game.ComManagerAnnouncementPanel.tips')" />
            <form class="chat-input">
                <textarea
                    v-model="announcement"
                    class="text-input"
                    ref="input"
                    @keydown.enter.exact.prevent="breakLine"
                    @keydown.enter.ctrl.exact.prevent="breakLine"
                    @keydown.enter.shift.exact.prevent="breakLine"
                />
            </form>
            <div class="send-button" v-if="announcement">
                <div class="actions">
                    <ActionButton
                        :key="ComManagerAnnouncementAction.key"
                        :action="ComManagerAnnouncementAction"
                        @click="executeTargetAction(ComManagerAnnouncementAction)"
                    />
                </div>
            </div>
        </div>
        <button class="exit" v-if="comManagerAnnouncementPanelOpen" @click="closeComManagerAnnouncementPanel">
            {{ $t('util.exit') }}
        </button>
    </div>
</template>

<script lang="ts">
import { Player } from "@/entities/Player";
import { defineComponent } from "vue";
import { ActionEnum } from "@/enums/action.enum";
import { Action } from "@/entities/Action";
import { mapActions, mapGetters } from "vuex";
import ActionButton from "@/components/Utils/ActionButton.vue";
import { getImgUrl } from "@/utils/getImgUrl";
import TerminalTips from "@/components/Game/Terminals/TerminalTips.vue";

export default defineComponent ({
    name: "ComManagerAnnouncementPanel",
    components : {
        ActionButton,
        TerminalTips
    },
    props: {
        player: {
            type: Player,
            required: true
        }
    },
    computed: {
        ...mapGetters({
            'comManagerAnnouncementPanelOpen': 'player/comManagerAnnouncementPanelOpen'
        }),
        ComManagerAnnouncementAction(): Action {
            return this.player.getActionByKeyOrThrow(ActionEnum.COM_MANAGER_ANNOUNCEMENT);
        }
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
            'closeComManagerAnnouncementPanel': 'player/closeComManagerAnnouncementPanel'
        }),
        breakLine(): void {
            // find current caret position
            const element = this.$refs.input as HTMLTextAreaElement;
            const caretPos = element.selectionStart;

            // insert \n at the caret position
            element.value = element.value.slice(0, caretPos) + "\n" + element.value.slice(caretPos);

            // move caret to the end of the inserted "//"
            element.selectionStart = element.selectionEnd = caretPos + 1;
        },
        getImgUrl,
        async executeTargetAction(action: Action): Promise<void> {
            if (!action.canExecute || !this.announcement) {
                return;
            }

            const params = { "announcement": this.formatBreakLines(this.announcement) };
            this.resetAnnouncement();

            await this.executeAction({ target: null, action, params });
            await this.closeComManagerAnnouncementPanel();
        },
        formatBreakLines(text: string): string {
            return text.replace(/\n/g, "//");
        },
        resetAnnouncement(): void {
            this.announcement = "";
        }
    },
    data() {
        return {
            ActionEnum,
            announcement: ""
        };
    }
});
</script>

<style  lang="scss" scoped>

.actions {
    flex-direction: row;
    justify-content: space-evenly;
}

.chat-input {
    display: flex;
    position: relative;

    .text-input {
        position: relative;
        flex: 1;
        resize: vertical;
        min-height: 180px;
        padding: 3px 5px;
        font-style: italic;
        opacity: 0.85;
        box-shadow: 0 1px 0 white;
        border: 1px solid #aad4e5;
        border-radius: 3px;
        margin: 6px;

        &:active,
        &:focus {
            font-style: initial;
            opacity: 1;
        }
    }
}

.terminal {
    position: relative;
    flex-direction: column;
    width: 100%;
    max-width: 424px;
    height: 460px;
    margin-bottom: 2em;
    color: $deepBlue;
    background: $brightCyan;

    //didn't @include the bevels because of the Exit button being outside the frame
    clip-path: polygon(6.5px 0, calc(100% - 6.5px ) 0, 100% 6.5px, 100% calc(100% + 2em), 0 calc(100% + 2em), 0 6.5px);

    h1 {
        font-size: 1rem;
        letter-spacing: 0.03rem;
        text-transform: uppercase;
        margin: 0;
        padding: 5px 10px;

        img { vertical-align: middle; }
    }

    & > div {
        overflow: auto;
        padding: 0 8px 5px;

        align-items: stretch;
        padding-bottom: .3em;

        @extend %game-scrollbar;
    }

    &::v-deep(em) {
        color: $red;
    }

    .radio-buttons-container {
        margin-top: 0.2em;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-evenly;

        label {
            margin-right: 0.5em;
            margin-bottom: 0.2em;
        }
    }

    .exit {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 120px;
        min-height: 22px;
        transform: translateY(100%);
        align-items: center;
        justify-content: center;
        padding: .2em;
        background: #232e6e;
        border-radius: 0 0 3px 3px;
        color: white;
        font-size: 1em;
        font-weight: 700;
        font-variant: small-caps;
        letter-spacing: 0.03em;
        text-decoration: none;
        text-align: center;
        text-shadow: 0 0 4px #15273c, 0 0 4px #15273c;

        &:hover, &:focus, &:active {
            background: $brightCyan;
        }
    }
}
</style>
