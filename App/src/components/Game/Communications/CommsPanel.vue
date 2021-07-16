<template>
    <div id="comms-panel">
        <ul class="tabs">
            <Tab
                v-for="(channel, id) in channels"
                :key="id"
                :type="channel.scope"
                :selected="currentChannel === channel"
                @select="changeChannel({ channel })"
            />
            <Tab
                v-if="displayNewTab"
                type="new"
                class="new-tab"
                @select="createPrivateChannel"
            />
        </ul>
        <div class="cycle-time">
            <img src="@/assets/images/comms/calendar.png"><span>Jour {{ day }} - Cycle {{ cycle }}</span>
        </div>

        <component :is="currentTabComponent" :channel="currentChannel" />
    </div>
</template>

<script lang="ts">
import TipsTab from "@/components/Game/Communications/TipsTab.vue";
import RoomEventsTab from "@/components/Game/Communications/RoomEventsTab.vue";
import FavouritesTab from "@/components/Game/Communications/FavouritesTab.vue";
import DiscussionTab from "@/components/Game/Communications/DiscussionTab.vue";
import PrivateTab from "@/components/Game/Communications/PrivateTab.vue";
import MushTab from "@/components/Game/Communications/MushTab.vue";
import Tab from "@/components/Game/Communications/Tab.vue";
import { Room } from "@/entities/Room";
import { mapActions, mapState, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import { ChannelType } from "@/enums/communication.enum";
import { Component, defineComponent } from "vue";

const MAX_PRIVATE_TABS_NB = 3;

export default defineComponent ({
    name: "CommsPanel",
    components: {
        TipsTab,
        DiscussionTab,
        FavouritesTab,
        PrivateTab,
        RoomEventsTab,
        MushTab,
        Tab
    },
    props: {
        day: Number,
        cycle: Number,
        room: Room
    },
    computed: {
        ...mapState('communication', [
            'currentChannel'
        ]),
        ...mapGetters('communication', [
            'channels'
        ]),
        displayNewTab(): boolean {
            if (! this.channels || ! this.channels.length) { return false; }
            return this.channels.filter((channel: Channel) => channel.scope === ChannelType.PRIVATE).length < MAX_PRIVATE_TABS_NB;
        },
        currentTabComponent(): Component {
            if (this.currentChannel instanceof Channel) {
                switch (this.currentChannel.scope) {
                case ChannelType.TIPS:
                    return TipsTab;
                case ChannelType.ROOM_LOG:
                    return RoomEventsTab;
                case ChannelType.PRIVATE:
                    return PrivateTab;
                case ChannelType.PUBLIC:
                default:
                    return DiscussionTab;
                }
            }
            return DiscussionTab;
        }
    },
    beforeMount(): void {
        this.loadChannels();
    },
    methods: {
        ...mapActions('communication', [
            'loadChannels',
            'changeChannel',
            'createPrivateChannel'
        ])
    }
});
</script>

<style lang="scss"> //Not scoped to apply to children components

#comms-panel {
    .chatbox-container {
        .unit {
            padding: 5px 0;
        }

        .banner {
            flex-direction: row;
            align-items: center;
            justify-content: center;
            min-height: 24px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            background: #a6eefb;

            span {
                flex: 1;
                text-align: center;
            }

            .expand {
                align-self: center;
                padding: 2px;
            }

            img { vertical-align: middle; }
        }
    }

    .timestamp {
        position: absolute;
        z-index: 2;
        right: 5px;
        bottom: 5px;
        font-size: 0.85em;
        font-style: italic;
        opacity: 0.5;
        float: right;
    }
}

</style>

<style lang="scss" scoped>

#comms-panel {
    position: relative;
    display: block;
    width: 404px;
    height: 460px;

    .tabs {
        float: left;

        .new-tab {
            opacity: 0.3;

            &::after {
                background: none;
            }

            &.active,
            &:hover,
            &:focus {
                opacity: 1;
            }
        }
    }

    /* TIMER STYLING */

    .cycle-time {
        flex-direction: row;
        align-items: center;
        margin: 0 12px;
        min-height: 25px;
        float: right;
        font-size: 0.7em;
        font-variant: small-caps;

        img { margin-right: 3px; }
    }
}

</style>
