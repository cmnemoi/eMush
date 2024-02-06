<template>
    <TabContainer id="private-discussion-tab" :channel="channel" :new-message-allowed = "newMessagesAllowed">
        <ActionButtons
            class="action-buttons"
            :actions="['refresh', 'invite', 'report', 'leave']"
            @leave="leavePrivateChannel(channel)"
            @invite="getInvitablePlayersToPrivateChannel(channel)"
            @report="openReportPopup()"
        />
        <ul class="participants">
            <li v-for="(participant, key) in channel.participants" :key="key">
                <img :src="characterBody(participant.character.key)">
            </li>
        </ul>
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
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import TabContainer from "@/components/Game/Communications/TabContainer.vue";
import Message from "@/components/Game/Communications/Messages/Message.vue";
import { characterEnum } from "@/enums/character";
import ActionButtons from "@/components/Game/Communications/ActionButtons.vue";
import { defineComponent } from "vue";

export default defineComponent ({
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
        newMessagesAllowed(): boolean | undefined
        {
            return this.channel?.newMessageAllowed;
        }
    },
    methods: {
        characterBody: function(character : string): string {
            const images = characterEnum[character];
            return images.body;
        },
        ...mapActions('communication', [
            'leavePrivateChannel',
            'loadMessages',
            'getInvitablePlayersToPrivateChannel'
        ]),
        ...mapActions('popup', [
            'openReportPopup'
        ])
    }
});
</script>

<style lang="scss" scoped>

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

</style>
