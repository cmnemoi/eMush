<template>
    <TabContainer id="neron-tab" :channel="channel" :new-message-allowed="newMessagesAllowed">
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

export default defineComponent({
    name: "NeronTab",
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
        newMessagesAllowed(): boolean | undefined {
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
#neron-tab {
    .unit {
        .message {
            background-color: rgba(85, 170, 255, 0.1);
            border-left: 3px solid rgb(85, 170, 255);
            padding-left: 10px;
            margin: 5px 0;
        }
    }
}
</style>
