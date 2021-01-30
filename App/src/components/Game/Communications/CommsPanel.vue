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

<script>
import TipsTab from "@/components/Game/Communications/TipsTab";
import RoomEventsTab from "@/components/Game/Communications/RoomEventsTab";
import FavouritesTab from "@/components/Game/Communications/FavouritesTab";
import DiscussionTab from "@/components/Game/Communications/DiscussionTab";
import PrivateTab from "@/components/Game/Communications/PrivateTab";
import MushTab from "@/components/Game/Communications/MushTab";
import Tab from "@/components/Game/Communications/Tab";
import { Room } from "@/entities/Room";
import { mapActions, mapState } from "vuex";
import { PRIVATE, PUBLIC, ROOM_LOG, TIPS } from '@/enums/communication.enum';
import { Channel } from "@/entities/Channel";


const MAX_PRIVATE_TABS_NB = 3;


export default {
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
            'channels',
            'currentChannel'
        ]),
        displayNewTab() {
            if (! this.channels || ! this.channels.length) { return false; }
            return this.channels.filter(channel => channel.scope === PRIVATE).length < MAX_PRIVATE_TABS_NB;
        },
        currentTabComponent() {
            if (this.currentChannel instanceof Channel) {
                switch (this.currentChannel.scope) {
                case TIPS:
                    return TipsTab;
                case ROOM_LOG:
                    return RoomEventsTab;
                case PRIVATE:
                    return PrivateTab;
                case PUBLIC:
                default:
                    return DiscussionTab;
                }
            }
            return DiscussionTab;
        }
    },
    beforeMount() {
        this.loadChannels();
    },
    methods: {
        ...mapActions('communication', [
            'loadChannels',
            'changeChannel',
            'createPrivateChannel'
        ])
    }
};
</script>

<style lang="scss"> //Not scoped to apply to children components

#comms-panel {
    .chatbox-container {
        .unit {
            padding: 5px 0;
        }

        .actions {
            flex-direction: row;
            justify-content: flex-end;
            align-items: stretch;

            a {
                @include button-style(0.83em, 400, initial);

                height: 100%;
                margin-left: 3px;
                img { padding: 0 0.2em 0 0; }
            }
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
