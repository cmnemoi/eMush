<template>
    <div v-if="isRoot" class="message main-message" @click="$emit('click')">
        <div class="char-portrait">
            <img :src="characterPortrait">
        </div>
        <p>
            <span class="author">{{ message.character.name }} :</span><span v-html="formatMessage(message.message)" />
        </p>
        <div class="actions">
            <a href="#"><img src="@/assets/images/comms/reply.png">Répondre</a>
            <a href="#"><img src="@/assets/images/comms/fav.png">Favori</a>
            <a href="#"><img src="@/assets/images/comms/alert.png">Plainte</a>
        </div>
        <span class="timestamp">{{ formatDate(message.date, {local: "fr-FR"}) }}</span>
    </div>
    <div v-if="!isRoot" class="message child-message" @click="$emit('click')">
        <p>
            <img :src="characterPortrait">
            <span class="author">{{ message.character.name }} :</span><span v-html="formatMessage(message.message)" />
        </p>
        <div class="actions">
            <a href="#"><img src="@/assets/images/comms/reply.png">Répondre</a>
            <a href="#"><img src="@/assets/images/comms/alert.png">Plainte</a>
        </div>
        <span class="timestamp">{{ formatDate(message.date, {local: "fr-FR"}) }}</span>
    </div>
</template>

<script>
import { formatText } from "@/utils/formatText";
import formatDistanceToNow from 'date-fns/formatDistanceToNow';
import { fr } from 'date-fns/locale';
import { Message } from "@/entities/Message";
import { characterEnum } from "@/enums/character";

export default {
    name: "Message",
    props: {
        message: Message,
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
        characterPortrait: function() {
            const images = characterEnum[this.message.character.key];
            return this.isRoot ? images.body : images.head;
        }
    },
    methods: {
        formatDate: (date) => {
            return formatDistanceToNow(date, { locale : fr });
        },
        formatMessage(value) {
            if (! value) return '';
            return formatText(value.toString());
        }
    }
};
</script>return

<style lang="scss" scoped>

.message {
    position: relative;
    align-items: flex-start;
    flex-direction: row;

    .char-portrait {
        align-items: flex-start;
        justify-content: flex-start;
        min-width: 36px;
        margin-top: 4px;
        padding: 6px 2px;
    }

    p:not(.timestamp) {
        position: relative;
        flex: 1;
        margin: 3px 0;
        padding: 4px 6px;
        border-radius: 3px;
        background: white;
        word-break: break-word;

        /deep/ em { color: #cf1830; } //Makes italic text red

        .author {
            color: #2081e2;
            font-weight: 700;
            font-variant: small-caps;
            padding-right: 0.25em;
        }
    }

    &.new p { //New messages styling
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

    &.main-message {

        p { min-height: 52px; }

        p::before { //Bubble triangle*/
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

        &.new p::before { border-right-color: #ea9104; }
    }

    &.child-message {
        margin-left: 50px;
        img { margin-right: 3px; }
        p { margin-top: 10px; }

        p::before { //Bubble triangle
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

        &::before {
            --border-radius: 5px;

            content: "";
            position: absolute;
            top: calc(0px - var(--border-radius));
            left: -36px;
            width: calc(28px + var(--border-radius));
            height: calc(26px + var(--border-radius));
            border-left: 1px solid #aad4e5;
            border-bottom: 1px solid #aad4e5;
            border-radius: var(--border-radius);
            clip-path:
                polygon(
                    0 var(--border-radius),
                    calc(100% - var(--border-radius)) var(--border-radius),
                    calc(100% - var(--border-radius)) 100%,
                    0 100%
                );
        }

        &:not(:last-of-type)::after {
            --border-radius: 5px;

            content: "";
            position: absolute;
            top: 25px;
            left: -36px;
            width: calc(28px + var(--border-radius));
            bottom: calc(-4px - var(--border-radius));
            border-left: 1px solid #aad4e5;
            border-top: 1px solid #aad4e5;
            border-radius: var(--border-radius);
            clip-path:
                polygon(
                    0 0,
                    calc(100% - var(--border-radius)) 0,
                    calc(100% - var(--border-radius)) calc(100% - var(--border-radius)),
                    0 calc(100% - var(--border-radius))
                );
        }
    }

    &.neron { //Neron messages styling

        .char-portrait {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 2;
            margin: 4px 6px;
        }

        p {
            background: #74cbf3;
            font-variant: small-caps;

            .author { color: inherit; }
        }

        &.main-message {
            p {
                padding-left: 46px;
                &::before { content: none; } //removes the bubble triangle
            }
        }

        &.child-message p::before { border-color: #74cbf3; }
    }

    .actions { //buttons styling
        visibility: hidden;
        opacity: 0;
        position: absolute;
        right: 3px;
        top: -3px;
        height: 14px;
        transition: visibility 0s 0.15s, opacity 0.15s 0s, top 0.15s 0s;
    }
}

.message:hover,
.message:focus,
.message:focus-within,
.message:active {
    .actions {
        visibility: visible;
        opacity: 1;
        top: 5px;
        transition: visibility 0s 0.5s, opacity 0.15s 0.5s, top 0.15s 0.5s;
    }
}

</style>
