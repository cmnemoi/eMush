<template>
    <div id="comms-panel">
        <ul class="tabs">
            <li
                v-for="(channel, id) in getChannels"
                :key="id"
                :class="getCurrentChannel === channel ? 'checked' : ''"
                @click="changeChannel({channel: channel})"
            >
                <img :src="channelIcon(channel)">
            </li>
            <li class="checked">
                <img src="@/assets/images/comms/private.png">
            </li>
            <li>
                <img src="@/assets/images/comms/mush.png">
            </li>
            <li>
                <img src="@/assets/images/comms/fav.png">
                <span><!-- new messages notifier goes here --></span>
            </li>
            <li class="newtab">
                <img src="@/assets/images/comms/newtab.png">
            </li>
        </ul>
        <div class="cycle-time">
            <img src="@/assets/images/comms/calendar.png"><span>Jour {{ day }} - Cycle {{ cycle }}</span>
        </div>

        <div class="tabs-content">
            <component :is="currentTabComponent(getCurrentChannel)" v-if="! loading" :channel="getCurrentChannel" />
        </div>
    </div>
</template>

<script>
import TipsTab from "@/components/Game/Communications/TipsTab";
import RoomEventsTab from "@/components/Game/Communications/RoomEventsTab";
import FavouritesTab from "@/components/Game/Communications/FavouritesTab";
import DiscussionTab from "@/components/Game/Communications/DiscussionTab";
import PrivateTab from "@/components/Game/Communications/PrivateTab";
import MushTab from "@/components/Game/Communications/MushTab";
import { Room } from "@/entities/Room";
import { mapActions, mapGetters } from "vuex";
import { PRIVATE, PUBLIC, ROOM_LOG, TIPS } from '@/enums/communication.enum';
import { Channel } from "@/entities/Channel";


export default {
    name: "CommsPanel",
    components: {
        TipsTab,
        DiscussionTab,
        FavouritesTab,
        PrivateTab,
        RoomEventsTab,
        MushTab
    },
    props: {
        day: Number,
        cycle: Number,
        room: Room
    },
    computed: {
        ...mapGetters('communication', [
            'getCurrentChannel',
            'getChannels'
        ]),
        ...mapGetters('player', [
            'loading'
        ])
    },
    beforeMount() {
        this.loadChannels();
    },
    methods: {
        ...mapActions('communication', [
            'loadChannels',
            'changeChannel',
            'createPrivateChannel'
        ]),
        currentTabComponent: (channel) => {
            if (channel instanceof Channel) {
                switch (channel.scope) {
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
        },
        channelIcon: (channel) => {
            if (channel instanceof Channel) {
                switch (channel.scope) {
                case TIPS:
                    return require('@/assets/images/comms/tip.png');
                case ROOM_LOG:
                    return require('@/assets/images/comms/local.png');
                case PRIVATE:
                    return require('@/assets/images/comms/private.png');
                case PUBLIC:
                default:
                    return require('@/assets/images/comms/wall.png');
                }
            }
            return DiscussionTab;
        }
    }
};
</script>

<style lang="scss"> //Not scoped to apply to children components

#comms-panel {
    .tabs-content {
        min-width: 100%;
        font-size: 0.8em;
    }

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

        .chatbox {
            overflow: auto;
            padding: 7px;
            color: #090a61;
        }

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

        .chat-expand {
            display: flex;
            padding: 4px;
            color: #67b000;
            border-radius: 3px;
            font-size: 0.8em;
            text-decoration: underline;

            &.active,
            &:hover,
            &:focus {
                background: #a6eefb;
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
}

</style>

<style lang="scss" scoped>

#comms-panel {
    position: relative;
    display: block;
    width: 404px;
    height: 460px;

    /* TABS STYLING */

    ul.tabs {
        float: left;

        li {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
            float: left;
            cursor: pointer;
            width: 31px;
            min-height: 25px;
            margin-right: 4px;
            * { z-index: 2; }

            &::after { //Background of the tab icons
                content: "";
                z-index: 1;
                position: absolute;
                width: 100%;
                height: 100%;
                background: #213578;

                @include corner-bezel(4.5px, 4.5px, 0);
            }

            &.newtab {
                opacity: 0.3;
                &::after { background: none !important; }
            }

            &.checked,
            &.active,
            &:hover,
            &:focus {
                opacity: 1;
                &::after { background: rgba(194, 243, 252, 1); }
            }

            span { //New message notifier
                position: absolute;
                top: -6px;
                right: 3px;
                font-size: 0.82em;
                font-weight: 600;
                text-shadow: 0 0 3px black, 0 0 3px black, 0 0 3px black;
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
