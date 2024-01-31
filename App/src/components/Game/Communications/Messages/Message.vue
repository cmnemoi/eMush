<template>
    <div
        v-if="isRoot && !isSystemMessage"
        :class="isNeronMessage ? 'message main-message neron' : 'message main-message'"
        @click="$emit('click')"
    >
        <div class="character-body">
            <img :src="characterPortrait">
        </div>
        <p class="text">
            <span class="author">{{ message.character.name }} :</span><span v-html="formatMessage(message.message)" />
        </p>
        <ActionButtons v-if="isPlayerAlive" class="actions" :actions="['reply']" />
        <span class="timestamp" style="position: absolute">{{ message.date }}</span>
    </div>
    <div
        v-if="isRoot && isSystemMessage"
        class="log"
        @click="$emit('click')"
    >
        <p class="text">
            <span v-html="formatMessage(message.message)" />
        </p>
        <span class="timestamp" style="position: absolute">{{ message.date }}</span>
    </div>
    <div
        v-else-if="!isRoot"
        :class="isHidden ? 'message child-message hidden' : 'message child-message'"
        @click="$emit('click')"
    >
        <p class="text">
            <img class="character-head" :src="characterPortrait">
            <span class="author">{{ message.character.name }} :</span><span v-html="formatMessage(message.message)" />
        </p>
        <ActionButtons v-if="isPlayerAlive" class="actions" :actions="['reply']" />
        <span class="timestamp" style="position: absolute">{{ message.date }}</span>
    </div>
</template>

<script lang="ts">
import ActionButtons from "@/components/Game/Communications/ActionButtons.vue";
import { formatText } from "@/utils/formatText";
import formatDistanceToNow from 'date-fns/formatDistanceToNow';
import { fr } from 'date-fns/locale';
import { mapGetters } from "vuex";
import { Message } from "@/entities/Message";
import { CharacterEnum, characterEnum } from "@/enums/character";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "Message",
    components: {
        ActionButtons
    },
    props: {
        message: {
            type: Message,
            required: true
        },
        isRoot: {
            type: Boolean,
            default: false
        }
    },
    emits: {
        // No validation
        click: null
    },
    computed: {
        ...mapGetters('player', [
            'player'
        ]),
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
            return !['finished'].includes(this.player.gameStatus);
        }
    },
    methods: {
        formatDate: (date: Date): string => {
            return formatDistanceToNow(date, { locale : fr });
        },
        formatMessage(value: string): string {
            if (! value) return '';
            return formatText(value.toString());
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

    &::v-deep(em), &::v-deep(a) {
        color: $red; // Make italics and links red
    }

    &.unread { // unread messages styling
        border-left: 2px solid #ea9104;

        &::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: -6px;
            min-height: 11px;
            width: 11px;
            background: transparent url('~@/assets/images/comms/thinklinked.png') center no-repeat;
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
    position: absolute;
    visibility: hidden;
    opacity: 0;
    z-index: 5;
    right: 3px;
    bottom: -2px;
    height: 14px;
    transition: visibility 0s 0.15s, opacity 0.15s 0s, bottom 0.15s 0s;
}

.message:hover,
.message:focus,
.message:focus-within,
.message:active {
    .actions {
        visibility: visible;
        opacity: 1;
        bottom: 7px;
        transition: visibility 0s 0.5s, opacity 0.15s 0.5s, bottom 0.15s 0.5s;
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

    &.new p::before { border-right-color: white; }
}

</style>
