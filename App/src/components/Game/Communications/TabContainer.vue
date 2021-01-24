<template>
    <div class="tab-content">
        <div class="chatbox-container">
            <MessageInput v-if="newMessageAllowed && ! loadingChannels" :channel="channel" />
            <div class="chatbox">
                <slot />
            </div>
            <span v-if="loading" class="loading">Loading...</span>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapState } from "vuex";
import { Channel } from "@/entities/Channel";
import MessageInput from "@/components/Game/Communications/Messages/MessageInput";

export default {
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
    }
};
</script>

<style lang="scss" scoped>

.tab-content {
    min-width: 100%;
    font-size: 0.8em;

    .chatbox-container {
        display: flex;
        position: relative;
        z-index: 2;
        height: 436px;
        margin-top: -1px;
        color: #090a61;
        line-height: initial;
        background: rgba(194, 243, 252, 1);

        @include corner-bezel(0, 6.5px, 0);
    }

    .chatbox {
        overflow: auto;
        padding: 7px;
        color: #090a61;
    }

    /* SCROLLBAR STYLING */
    .chatbox,
    .chatbox-container {
        --scrollbarBG: white;
        --thumbBG: #090a61;

        scrollbar-width: thin;
        scrollbar-color: var(--thumbBG) var(--scrollbarBG);
        &::-webkit-scrollbar { width: 6px; }
        &::-webkit-scrollbar-track { background: var(--scrollbarBG); }
        &::-webkit-scrollbar-thumb { background-color: var(--thumbBG); }
    }

    .loading {
        padding: 7px;
        color: #090a61;
        font-style: italic;
        text-align: right;
        margin-top: auto;
    }
}

</style>
