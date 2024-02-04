<template>
    <div class="tab-content">
        <div class="chatbox-container" :class="{ 'pirated': isChannelPirated() }">
            <MessageInput v-if="newMessageAllowed && ! loadingChannels" :channel="channel" />
            <div class="chatbox">
                <slot />
            </div>
            <span v-if="loading" class="loading">{{ $t('loading') }}</span>
        </div>
    </div>
</template>

<script lang="ts">
import { mapGetters, mapState } from "vuex";
import { Channel } from "@/entities/Channel";
import MessageInput from "@/components/Game/Communications/Messages/MessageInput.vue";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "DiscussionTab",
    components: {
        MessageInput
    },
    props: {
        channel: Channel,
        newMessageAllowed: Boolean
    },
    computed: {
        ...mapGetters('communication', [
            'loading'
        ]),
        ...mapState('communication', [
            'loadingChannels'
        ])
    },
    methods: {
        isChannelPirated(): string
        {
            console.log((this.channel?.piratedPlayer != null) ? 'pirated' : '');
            return (this.channel?.piratedPlayer != null) ? 'pirated' : '';
        },
    }
});
</script>

<style lang="scss" scoped>

.tab-content {
    min-width: 100%;
    max-height: 115%;

    .chatbox-container {
        display: flex;
        position: relative;
        z-index: 2;
        margin-top: -1px;
        color: $deepBlue;
        line-height: initial;
        background: rgba(194, 243, 252, 1);

        @include corner-bezel(0, 6.5px, 0);

        &.pirated {
            border: 2px solid rgba(255, 66, 89, 1);
        }
    }

    .chatbox {
        height: 436px;
        min-height: 436px;
        max-height: 1080px;
        overflow: auto;
        padding: 7px;
        color: $deepBlue;
        resize: x;
    }

    .chat-input + .chatbox { // If there is an input box, the chatbox will subtract its height
        height: calc(436px - 59px);
        min-height: calc(436px - 59px);
    }

    /* SCROLLBAR STYLING */
    .chatbox,
    .chatbox-container {
        resize: vertical;
        @extend %game-scrollbar;
    }

    .loading {
        padding: 7px;
        color: $deepBlue;
        font-style: italic;
        text-align: right;
        margin-top: auto;
    }
}

</style>
