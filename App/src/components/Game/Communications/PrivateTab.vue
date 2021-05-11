<template>
    <TabContainer id="private-discussion-tab" :channel="channel" new-message-allowed>
        <ActionButtons
            class="action-buttons"
            :actions="['refresh', 'invite', 'report', 'leave']"
            @leave="leavePrivateChannel(channel)"
        />
        <ul class="participants" v-for="(participant, key) in channel.participants" :key="key">
            <li><img :src="characterBody(participant.character.key)"></li>
        </ul>
        <section v-for="(message, id) in messages" :key="id" class="unit">
            <Message :message="message" :is-root="true" />
        </section>
    </TabContainer>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import { Channel } from "@/entities/Channel";
import TabContainer from "@/components/Game/Communications/TabContainer";
import Message from "@/components/Game/Communications/Messages/Message";
import {characterEnum} from "@/enums/character";
import ActionButtons from "@/components/Game/Communications/ActionButtons";

export default {
    name: "PrivateTab",
    components: {
        ActionButtons,
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
    },
    methods: {
        characterBody: function(character) {
            console.log(character)
            const images = characterEnum[character];
            return images.body;
        },
        ...mapActions('communication', [
            'leavePrivateChannel',
            'loadMessages'
        ])
    },
    beforeMount() {
        this.loadMessages({ channel: this.channel });
    },
};
</script>

<style lang="scss" scoped>

/* --- PROVISIONAL UNTIL LINE 203 --- */

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
            color: #2081e2;
            font-weight: 700;
            font-variant: small-caps;
            padding-right: 0.25em;
        }

        em { color: #cf1830; }
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
            background: transparent url('~@/assets/images/comms/thinklinked.png') center no-repeat;
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

    .text-log {
        margin: 0;
        font-size: 0.95em;
        >>> img { vertical-align: middle; }
    }
}

/* --- END OF PROVISIONAL --- */

#private-discussion-tab {
    .unit {
        padding: 5px 0;
    }

    .participants {
        flex-direction: row;
        margin: 8px 0;

        li { width: 28px; }
    }
}

#private-discussion-tab .unit > .message:nth-of-type(odd) {
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
