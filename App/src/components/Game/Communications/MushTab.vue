<template>
    <TabContainer id="mush-tab" :channel="channel" :new-message-allowed = "newMessagesAllowed">
        <section class="unit">
            <Message
                v-for="(message, id) in messages"
                :key="id"
                :message="message"
                :is-root="true"
            />
        </section>
    </TabContainer>
</template>

<script lang="ts">
import { Channel } from "@/entities/Channel";
import TabContainer from "@/components/Game/Communications/TabContainer.vue";
import { defineComponent } from "vue";
import Message from "@/components/Game/Communications/Messages/Message.vue";
import { mapActions, mapGetters } from "vuex";

export default defineComponent ({
    name: "MushTab",
    components: {
        Message,
        TabContainer
    },
    props: {
        channel: Channel
    },
    computed: {
        ...mapGetters('communication', [
            'messages'
        ]),
        newMessagesAllowed(): boolean | undefined
        {
            return this.channel?.newMessageAllowed;
        }
    },
    methods: {
        ...mapActions('communication', [
            'loadMessages'
        ])
    }
});
</script>

<style lang="scss" scoped>

/* --- PROVISIONAL UNTIL LINE 185 --- */

.message {
    position: relative;
    align-items: flex-start;
    flex-direction: row;

    .char-portrait {
        align-items: flex-start;
        justify-content: flex-start;
        min-width: 36px;
        padding: 2px;
    }

    p:not(.timestamp) {
        position: relative;
        flex: 1;
        margin: 3px 0;
        padding: 4px 6px;
        border-radius: 3px;
        background: white;
        word-break: break-word;

        .author {
            color: $blue;
            font-weight: 700;
            font-variant: small-caps;
            padding-right: 0.25em;
        }

        em { color: $red; }
    }

    &.new p {
        border-left: 2px solid #ea9104;

        &::after {
            content: "";
            position: absolute;
            top: 7px;
            left: -6px;
            height: 11px;
            width: 11px;
            background: transparent url('/src/assets/images/comms/thinklinked.png') center no-repeat;
        }
    }

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

    &.new p {
        &::before { border-right-color: #ea9104; }
        &::after { top: 22px; }
    }
}

/* ----- */

.log {
    position: relative;
    padding: 4px 5px;
    margin: 1px 0;
    border-bottom: 1px solid rgb(170, 212, 229);

    p {
        margin: 0;
        font-size: 0.95em;
        &::v-deep(img) { vertical-align: middle; }
    }
}

/* --- END OF PROVISIONAL --- */

#mush-tab {
    .unit {
        padding: 5px 0;
    }

    &::v-deep(.chat-input .submit-button) { //change the submit button color
        $color: #7152d9;
        $hover-color: #9469fa;

        background: $color;
        background:
            linear-gradient(
                0deg,
                darken(adjust-hue($color, 13), 5.49) 2%,
                $color 6%,
                $color 46%,
                lighten(adjust-hue($color, -6), 3.5) 54%,
                lighten(adjust-hue($color, -6), 3.5) 94%,
                lighten(desaturate($color, 25), 15.49) 96%
            );

        &:hover,
        &:focus {
            background: $hover-color;
            background:
                linear-gradient(
                    0deg,
                    darken(adjust-hue($hover-color, 14), 3.92) 2%,
                    $hover-color 6%,
                    $hover-color 46%,
                    lighten(adjust-hue($hover-color, -4), 1) 54%,
                    lighten(adjust-hue($hover-color, -4), 1) 94%,
                    lighten(desaturate($hover-color, 18.1), 13.14) 96%
                );
        }
    }

    .actions {
        flex-direction: row;
        justify-content: flex-end;
        align-items: stretch;

        a {
            @include button-style(0.83em, 400, initial);

            height: 100%;
            margin-left: 3px;
        }
    }

    .banner {
        margin-bottom: 6px;
        background: #e7bacc !important;
    }
}

#mush-tab .unit > .message:nth-of-type(odd) {
    flex-direction: row-reverse;

    .char-portrait { align-items: flex-end; }

    .timestamp { right: 41px; }

    p::before {
        left: initial;
        right: -8px;
        transform: rotate(180deg);
    }

    &.new p::before { border-right-color: white; }
}

</style>
