<template>
    <TabContainer
        id="mush-tab"
        :channel="channel"
        :new-message-allowed="newMessagesAllowed"
        :class="{ 'no-select': !isPlayerMush }">
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
        ...mapGetters('player', [
            'player'
        ]),
        newMessagesAllowed(): boolean | undefined
        {
            return this.channel?.newMessageAllowed;
        },
        isPlayerMush(): boolean | undefined
        {
            return this.player?.isMush;
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
@use "sass:color";

#mush-tab {
    .unit {
        padding: 5px 0;
    }

    &.no-select {
        user-select: none;
    }

    :deep(.chat-input .submit-button) { //change the submit button color
        $color: #7152d9;
        $hover-color: #9469fa;

        background: $color;
        background:
            linear-gradient(
                0deg,
                color.adjust(color.adjust($color, $hue: 13deg), $lightness: -5.49%) 2%,
                $color 6%,
                $color 46%,
                color.adjust(color.adjust($color, $hue: -6deg), $lightness: 3.5%) 54%,
                color.adjust(color.adjust($color, $hue: -6deg), $lightness: 3.5%) 94%,
                color.adjust(color.adjust($color, $saturation: -25%), $lightness: 15.49%) 96%
            );

        &:hover,
        &:focus {
            background: $hover-color;
            background:
                linear-gradient(
                    0deg,
                    color.adjust(color.adjust($hover-color, $hue: 14deg), $lightness: -3.92%) 2%,
                    $hover-color 6%,
                    $hover-color 46%,
                    color.adjust(color.adjust($hover-color, $hue: -4deg), $lightness: 1%) 54%,
                    color.adjust(color.adjust($hover-color, $hue: -4deg), $lightness: 1%) 94%,
                    color.adjust(color.adjust($hover-color, $saturation: -18.1%), $lightness: 13.14%) 96%
                );
        }
    }
}

</style>
