<template>
    <ModerationActionPopup
        :moderation-dialog-visible="moderationDialogVisible"
        :action="{ key: 'moderation.sanction.delete_message', value: 'delete_message' }"
        @close="closeModerationDialog"
        @submit-sanction="deleteMessage"
    />
    <ReportPopup
        :report-dialog-visible="reportPopupVisible"
        :select-player="true"
        @close=closeReportDialog
        @submit-report=submitComplaint
    />
    <div
        v-if="isRoot && !isSystemMessage"
        :class="isNeronMessage ? 'message main-message neron' : 'message main-message'"
        @click="$emit('reply')"
        @mouseover="read(message)"
    >
        <div class="character-body">
            <img :src="characterPortrait" alt="Character image">
        </div>
        <p :class="['text', { unread: message.isUnread, read: !message.isUnread }]">
            <span class="author">{{ message.character.name }} :</span><span v-html="formatMessage(message.message)" />
            <span class="timestamp">{{ message.date }}</span>
        </p>
        <div class="actions" @click.stop>
            <ActionButtons
                v-if="isPlayerAlive && channel.supportsReplies()"
                :actions="['reply']"
                @reply="$emit('reply')"
            />
            <ActionButtons
                v-if="isPlayerAlive && channel.supportsFavorite()"
                :actions="channel.isFavorite() ? ['unfavorite'] : ['favorite']"
                @favorite="favorite(message)"
                @unfavorite="unfavorite(message)"
            />
            <ActionButtons
                :actions="['report']"
                @report=openReportDialog
            />
            <ActionButtons
                v-if="adminMode"
                :actions="['delete']"
                @delete="openModerationDialog('delete_message')"
            />
        </div>
    </div>
    <div
        v-if="isRoot && isSystemMessage"
        class="log"
        @click="$emit('reply')"
        @mouseover="read(message)"
    >
        <p :class="['text', { unread: message.isUnread }]">
            <span v-html="formatMessage(message.message)" />
            <span class="timestamp">{{ message.date }}</span>
        </p>
    </div>
    <div
        v-else-if="!isRoot"
        :class="isHidden ? 'message child-message hidden' : 'message child-message'"
        @click="$emit('reply')"
        @mouseover="read(message)"
    >
        <p :class="['text', { unread: message.isUnread }]">
            <img class="character-head" :src="characterPortrait" alt="Character portrait">
            <span class="author">{{ message.character.name }} :</span><span v-html="formatMessage(message.message)" />
            <span class="timestamp">{{ message.date }}</span>
        </p>
        <div class="actions" @click.stop>
            <ActionButtons
                v-if="isPlayerAlive && channel.supportsReplies()"
                :actions="['reply']"
                @reply="$emit('reply')"
            />
            <ActionButtons
                v-if="isPlayerAlive && channel.supportsFavorite()"
                :actions="channel.isFavorite() ? ['unfavorite'] : ['favorite']"
                @favorite="favorite(message)"
                @unfavorite="unfavorite(message)"
            />
            <ActionButtons
                :actions="['report']"
                @report=openReportDialog
            />
            <ActionButtons
                v-if="adminMode"
                :actions="['delete']"
                @delete="openModerationDialog('delete_message')"
            />
        </div>
    </div>
</template>

<script lang="ts">
import ActionButtons from "@/components/Game/Communications/ActionButtons.vue";
import { formatText } from "@/utils/formatText";
import formatDistanceToNow from 'date-fns/formatDistanceToNow';
import { fr } from 'date-fns/locale';
import { mapActions, mapGetters } from "vuex";
import { Message } from "@/entities/Message";
import { CharacterEnum, characterEnum } from "@/enums/character";
import { defineComponent } from "vue";
import ModerationService from "@/services/moderation.service";
import ModerationActionPopup from "@/components/Moderation/ModerationActionPopup.vue";
import ReportPopup from "@/components/Moderation/ReportPopup.vue";

export default defineComponent ({
    name: "Message",
    components: {
        ActionButtons,
        ModerationActionPopup,
        ReportPopup
    },
    data() {
        return {
            moderationDialogVisible: false,
            reportPopupVisible: false
        };
    },
    props: {
        message: {
            type: Message,
            required: true
        },
        isRoot: {
            type: Boolean,
            default: false
        },
        isReplyable: {
            type: Boolean,
            default: false
        },
        adminMode: {
            type: Boolean,
            default: false
        }
    },
    emits: {
        // No validation
        click: null,
        report: null
    },
    computed: {
        ...mapGetters({
            channel: 'communication/currentChannel',
            player: 'player/player',
            readMessageMutex: 'communication/readMessageMutex'
        }),
        characterPortrait: function(): string| null {
            if (this.message.character.key !== null) {
                const images = characterEnum[this.message.character.key];
                return this.isRoot ? images.body : images.head;
            }
            return null;
        },
        isNeronMessage: function(): boolean {
            return this.message.character.key === CharacterEnum.NERON;
        },
        isSystemMessage: function(): boolean {
            return this.message.character.key === null;
        },
        isHidden: function(): boolean {
            return this.message.isHidden;
        },
        isPlayerAlive: function(): boolean {
            if (!this.player) {
                return false;
            }
            return !['finished'].includes(this.player.gameStatus);
        }
    },
    methods: {
        ...mapActions({
            acquireReadMessageMutex: 'communication/acquireReadMessageMutex',
            favoriteMessage: 'communication/favoriteMessage',
            readMessage: 'communication/readMessage',
            releaseReadMessageMutex: 'communication/releaseReadMessageMutex',
            unfavoriteMessage: 'communication/unfavoriteMessage',
            loadReportablePlayers: 'moderation/loadReportablePlayers',
            reportMessage: 'moderation/reportMessage'
        }),
        formatDate: (date: Date): string => {
            return formatDistanceToNow(date, { locale : fr });
        },
        formatMessage(value: string): string {
            return formatText(value.toString());
        },
        deleteMessage(params: any) {
            if (this.message.id === null) {
                return;
            }
            ModerationService.deleteMessage(this.message.id, params);
            this.moderationDialogVisible = false;
        },
        openModerationDialog() {
            this.moderationDialogVisible = true;
        },
        closeModerationDialog() {
            this.moderationDialogVisible = false;
        },
        openReportDialog() {
            this.reportPopupVisible = true;
            this.loadReportablePlayers();
        },
        closeReportDialog() {
            this.reportPopupVisible = false;
        },
        async submitComplaint(params: URLSearchParams) {
            await this.reportMessage({ messageId: this.message.id, params: params });
            this.reportPopupVisible = false;
        },
        async read(message: Message) {
            if (this.adminMode) return;

            if (message.isUnread && !this.readMessageMutex) {
                await this.acquireReadMessageMutex();
                await this.readMessage(message);
                await this.releaseReadMessageMutex();
            }
        },
        async favorite(message: Message) {
            await this.favoriteMessage(message);
        },
        async unfavorite(message: Message) {
            await this.unfavoriteMessage(message);
        }
    }
});
</script>

<style lang="scss" scoped>

.log {
    position: relative;
    padding: 4px 5px;
    margin: 1px 0;
    border-bottom: 1px solid rgb(170, 212, 229);

    p {
        margin: 0;
        font-size: .95em;
    }
}

.message {
    position: relative;
    align-items: flex-start;
    flex-direction: row;

    .actions { flex-direction: row; }
}

.character-body {
    align-items: flex-start;
    justify-content: flex-start;
    min-width: 36px;
    margin-top: 4px;
    padding: 6px 2px;
}

.author {
    color: $blue;
    font-weight: 700;
    font-variant: small-caps;
    padding-right: 0.25em;
}

.text {
    position: relative;
    flex: 1;
    margin: 3px 0;
    padding: 4px 6px;
    border-radius: 3px;
    background: white;
    word-break: break-word;

    :deep(em) {
        color: $red;
    }

    :deep(a) {
        color: $deepGreen;

        &:hover {
            color: $green;
        }
    }

    &.read {
        border-left: 2px solid transparent;
        transition: 0.1s ease-in-out border-left;
    }

    &.unread { // unread messages styling
        border-left: 2px solid #ea9104;
        transition: none;

        &::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: -6px;
            min-height: 11px;
            width: 11px;
            background: transparent url('/src/assets/images/comms/thinklinked.png') center no-repeat;
        }
    }
}

.main-message {
    .text {
        min-height: 52px;

        &::before { //Bubble triangle*/
            $size: 8px;

            content: "";
            position: absolute;
            top: 4px;
            left: -$size;
            width: 0;
            height: 0;
            border-top: $size solid transparent;
            border-bottom: $size solid transparent;
            border-right: $size solid white;
        }

        &.unread {
            &::before {
                border-right-color: #ea9104;
            }
        }
    }
}

.child-message {
    margin-left: 50px;

    .character-head {
        margin-right: 3px;
    }

    .text {
        margin-top: 10px;
    }

    .text::before { //Bubble triangle
        $size: 8px;

        content: "";
        position: absolute;
        top: -$size;
        left: 4px;
        width: 0;
        height: 0;
        border-left: $size solid transparent;
        border-right: $size solid transparent;
        border-bottom: $size solid white;
    }

    /* MESSAGES LINKTREE */

    --border-radius: 5px;

    &::before, &::after {
        content: "";
        pointer-events: none;
        position: absolute;
        bottom: calc(-4px - var(--border-radius));
        left: -36px;
        width: calc(28px + var(--border-radius));
        border: 1px solid #aad4e5;
        border-right-width: 0;
        border-radius: var(--border-radius);
    }

    &::before {
        top: calc(0px - var(--border-radius));
        height: calc(26px + var(--border-radius));
        border-top-width: 0;
        clip-path:
            polygon(
                    0 var(--border-radius),
                    calc(100% - var(--border-radius)) var(--border-radius),
                    calc(100% - var(--border-radius)) 100%,
                    0 100%
            );
    }

    /*&:not(:last-of-type)::after {*/
    &::after {
        top: 25px;
        border-bottom-width: 0;
        clip-path:
            polygon(
                    0 0,
                    calc(100% - var(--border-radius)) 0,
                    calc(100% - var(--border-radius)) calc(100% - var(--border-radius)),
                    0 calc(100% - var(--border-radius))
            );
    }

    &:last-of-type::after { content: none; }
}

.hidden {
    display: none;
}

.neron { // Neron messages styling

    .character-body {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 2;
        margin: 4px 6px;
    }

    .author {
        color: inherit;
    }

    .text {
        background: #74cbf3;
        font-variant: small-caps;
    }

    &.main-message {
        .text {
            padding-left: 52px;
            &::before { content: none; } // removes the bubble triangle
        }
    }

    &.child-message {
        .text::before {
            border-color: #74cbf3;
        }
    }
}

.actions { //buttons styling
    $delay-hide: 0.15s;

    position: absolute;
    visibility: hidden;
    opacity: 0;
    z-index: 5;
    right: 3px;
    bottom: -2px;
    height: 18px;
    transition: visibility 0s $delay-hide, opacity $delay-hide 0s, bottom $delay-hide 0s;
}

.message:hover,
.message:focus,
.message:focus-within,
.message:active {
    .actions {
        $delay-show: 0.3s;

        visibility: visible;
        opacity: 1;
        bottom: 7px;
        transition: visibility 0s $delay-show, opacity 0.15s $delay-show, bottom 0.15s $delay-show;
    }
}


#private-discussion-tab .unit > .message:nth-of-type(odd) { // alterning left and right style in private channels
    flex-direction: row-reverse;

    .character-body { transform: scaleX(-1); }

    .timestamp { right: 41px; }

    .actions { right: 39px; }

    p::before {
        left: initial;
        right: -8px;
        transform: rotate(180deg);
    }

    &.new p::before, &.unread p::before { border-right-color: white; }
}

</style>
