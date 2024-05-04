<template>
    <div id="comms-panel" :class="{toomany: channels.length > 8}" >
        <Tippy tag="div" class="cycle-time">
            <img :src="getImgUrl('comms/calendar.png')">
            <span>{{ calendar?.dayName }} {{ calendar?.day }} - <br />{{ calendar?.cycleName }} {{ calendar?.cycle }}</span>
            <span class="mobile">{{ calendar?.day }}-{{ calendar?.cycle }}</span>
            <template #content>
                <h1 v-html="formatContent(calendar.name)" />
                <p v-html="formatContent(calendar.description)" />
            </template>
        </Tippy>
        <ul class="tabs">
            <Tab
                v-for="(channel, id) in channels"
                :class="channel.scope"
                :key="id"
                :type="channel.scope"
                :is-pirated="isChannelPirated(channel)"
                :name="channel.name"
                :description="channel.description"
                :selected="isChannelSelected(channel)"
                :number-of-new-messages="channel.numberOfNewMessages"
                @select="changeChannel({ channel })"
            />
        </ul>
        <component :is="currentTabComponent" :channel="currentChannel" :calendar="calendar" />
        <button class="action-button" @click="loadMoreMessages()" v-if="canLoadMoreMessages()">
            {{ $t('game.communications.loadMoreMessages') }}
        </button>
        <button class="action-button" @click="markAsRead()">
            {{ $t('game.communications.markChannelAsRead') }}
        </button>
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
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import { ChannelType } from "@/enums/communication.enum";
import { Component, defineComponent } from "vue";
import { GameCalendar } from "@/entities/GameCalendar";
import { getImgUrl } from "@/utils/getImgUrl";

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
        room: Room,
        calendar: GameCalendar
    },
    computed: {
        ...mapGetters({
            currentChannel: 'communication/currentChannel',
            channels: 'communication/channels',
            messages: 'communication/messages',
            currentChannelNumberOfNewMessages: 'communication/currentChannelNumberOfNewMessages',
            player: 'player/player'
        }),
        currentTabComponent(): Component {
            if (this.currentChannel instanceof Channel) {
                switch (this.currentChannel.scope) {
                // TODO: not implemented yet
                // case ChannelType.TIPS:
                //     return TipsTab;
                case ChannelType.ROOM_LOG:
                    return RoomEventsTab;
                case ChannelType.MUSH:
                    return MushTab;
                case ChannelType.PRIVATE:
                    return PrivateTab;
                case ChannelType.FAVORITES:
                    return FavouritesTab;
                case ChannelType.PUBLIC:
                default:
                    return DiscussionTab;
                }
            }
            return DiscussionTab;
        }
    },
    async beforeMount(): Promise<void> {
        if (this.player.isDead()) {
            await this.loadDeadPlayerChannels();
        } else {
            await this.loadAlivePlayerChannels();
        }
    },
    methods: {
        ...mapActions({
            changeChannel: 'communication/changeChannel',
            loadAlivePlayerChannels: 'communication/loadAlivePlayerChannels',
            loadDeadPlayerChannels: 'communication/loadDeadPlayerChannels',
            loadMoreMessages: 'communication/loadMoreMessages',
            markAllRoomLogsAsRead: 'communication/markAllRoomLogsAsRead',
            markCurrentChannelAsRead: 'communication/markCurrentChannelAsRead'
        }),
        canLoadMoreMessages(): boolean {
            return this.currentChannel.isChannelWithPagination() &&
                this.messages.length % Channel.MESSAGE_LIMIT === 0;
        },
        getImgUrl,
        isChannelPirated(channel: Channel): boolean
        {
            return channel.piratedPlayer != null;
        },
        isChannelSelected(channel: Channel): boolean
        {
            return (this.currentChannel.scope === channel.scope &&
                this.currentChannel.id === channel.id) &&
                this.currentChannel.piratedPlayer === channel.piratedPlayer;
        },
        async markAsRead(): Promise<void> {
            if (this.currentChannel.scope === ChannelType.ROOM_LOG) {
                await this.markAllRoomLogsAsRead(this.currentChannel);
            } else {
                await this.markCurrentChannelAsRead(this.currentChannel);
            }
        }
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
            background: $lightCyan;

            span {
                flex: 1;
                text-align: center;
                font-size: .92em;
            }

            .expand {
                align-self: center;
                padding: 2px;
            }

            img { vertical-align: middle; }
        }
    }

    .timestamp {
        text-align: end;
        padding-top: 0.2em;
        font-size: 0.85em;
        letter-spacing: 0.03em;
        font-style: italic;
        font-variant: initial;
        opacity: 0.65;
        float: right;
    }
}
</style>

<style lang="scss" scoped>

#comms-panel {
    position: relative;
    display: block;
    width: 404px;
    min-height: 460px;

    .tabs {
        float: left;
        max-width: 70%;

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

    &.toomany .tabs {
        flex-wrap: wrap;
        padding-bottom: 0.8em;
        gap:.5em 0;

        &:deep(.tab::after) { @include corner-bezel(4.5px); }
    }

    @media screen and (max-width: $breakpoint-desktop-m) and (orientation: portrait) { width: 100%; }
}

/* TIMER STYLING */

.cycle-time {
    flex-direction: row;
    align-items: center;
    margin: 0 12px;
    min-height: 25px;
    float: right;
    font-size: 0.8em;
    font-variant: small-caps;

    img { margin-right: 3px; }

    span.mobile, br { display: none; }

    @media screen and (max-width: $breakpoint-desktop-l) { br { display: initial; } }

    @media screen and (max-width: $breakpoint-desktop-m) {
        span:not(.mobile) { display: none; }
        span.mobile {display: initial; }
    }
}

.action-button {
    float: right;
}

</style>
