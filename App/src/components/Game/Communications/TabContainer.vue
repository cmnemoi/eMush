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
    }
});
</script>

<style lang="scss" scoped>

.tab-content {
    min-width: 100%;

    .chatbox-container {
        display: flex;
        position: relative;
        z-index: 2;
        height: 436px;
        margin-top: -1px;
        color: $deepBlue;
        line-height: initial;
        background: rgba(194, 243, 252, 1);

        @include corner-bezel(0, 6.5px, 0);
    }

    .chatbox {
        overflow: auto;
        padding: 7px;
        color: $deepBlue;
    }

    /* SCROLLBAR STYLING */
    .chatbox,
    .chatbox-container {
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
