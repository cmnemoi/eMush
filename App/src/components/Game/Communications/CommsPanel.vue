<template>
    <div class="comms-panel">
        <div class="comms-panel-banner">
            <div class="tabs">
                <Tab
                    v-for="(channel, id) in channels"
                    :class="channel.scope"
                    :key="id"
                    :type="channel.scope"
                    :is-pirated="isChannelPirated(channel)"
                    :name="channel.name"
                    :description="channel.description"
                    :selected="isChannelSelected(channel)"
                    :number-of-new-messages="numberOfNewMessages(channel)"
                    :flashing="channel.flashing"
                    @select="changeChannel({ channel }); readTipsChannel(channel)"
                />
            </div>
            <Tippy tag="div" class="cycle-time" :class="{ 'shrink': channels.length > 11}">
                <img :src="getImgUrl('comms/calendar.png')">
                <span class="normal">{{ calendar?.dayName }} {{ calendar?.day }} - {{ calendar?.cycleName }} {{ calendar?.cycle }}</span>
                <span class="shrink">{{ calendar?.day }}-{{ calendar?.cycle }}</span>
                <template #content>
                    <h1 v-html="formatContent(calendar.name)" />
                    <p v-html="formatContent(calendar.description)" />
                </template>
            </Tippy>
        </div>
        <component :is="currentTabComponent" :channel="currentChannel" :calendar="calendar" />
        <button class="action-button" @click="loadMoreMessages()" v-if="canLoadMoreMessages()">
            {{ $t('game.communications.loadMoreMessages') }}
        </button>
        <button class="action-button" @click="markAsRead()" v-if="currentChannel.isNotTipsChannel()">
            {{ $t('game.communications.markChannelAsRead') }}
        </button>
        <Tippy tag="button" class="action-button" @click="exportChannelasClipboard()">
            📋
            <template #content>
                <h1 v-html="$t('game.communications.exportChannelAsClipboard')"/>
                <p v-html="$t('game.communications.exportChannelAsClipboardDescription')"/>
            </template>
        </Tippy>
        <Tippy tag="button" class="action-button" @click="exportChannelasPDF()">
            🖨️
            <template #content>
                <h1 v-html="$t('game.communications.exportChannelAsPDF')"/>
                <p v-html="$t('game.communications.exportChannelAsPDFDescription')"/>
            </template>
        </Tippy>
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
import { exportChannelToPDF, exportChannelToClipboard } from "@/services/export-channel-to-pdf.service";
import { Tippy } from "vue-tippy";

export default defineComponent ({
    name: "CommsPanel",
    components: {
        TipsTab,
        DiscussionTab,
        FavouritesTab,
        PrivateTab,
        RoomEventsTab,
        MushTab,
        Tab,
        Tippy
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
                case ChannelType.TIPS:
                    return TipsTab;
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
            markCurrentChannelAsRead: 'communication/markCurrentChannelAsRead',
            markTipsChannelAsRead: 'communication/markTipsChannelAsRead'
        }),
        canLoadMoreMessages(): boolean {
            return this.currentChannel.isChannelWithPagination();
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
        },
        async exportChannelasClipboard() : Promise<void> {
            const chatbox = document.querySelector('.chatbox') as HTMLElement;
            if (!chatbox) {
                console.error('Chatbox not found');
                return;
            }

            await exportChannelToClipboard(chatbox);
        },
        async exportChannelasPDF() : Promise<void> {
            const chatbox = document.querySelector('.chatbox') as HTMLElement;
            if (!chatbox) {
                console.error('Chatbox not found');
                return;
            }

            await exportChannelToPDF(chatbox);
        },
        numberOfNewMessages(channel: Channel): number {
            return channel.referenceId === this.currentChannel.referenceId ? this.currentChannelNumberOfNewMessages : channel.numberOfNewMessages;
        },
        async readTipsChannel(channel: Channel): Promise<void> {
            if (!channel.isTipsChannel()) {
                return;
            }

            await this.markTipsChannelAsRead(channel);
        }
    }
});
</script>


<style lang="scss"> //Not scoped to apply to children components

.comms-panel {
    margin-top: 2px;

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
.comms-panel {
    position: relative;
    display: block;
    width: 100%;
    min-height: 460px;

    @media screen and (max-width: $breakpoint-desktop-m) and (orientation: portrait) {
        margin-top: 3px;
    }
}

.comms-panel-banner {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    max-width: 100%;
    min-width: 0;
    gap: 5px;

    .tabs {
        min-width: 0;
        flex-direction: row;
        gap: 3px;
    }

    .cycle-time {
        flex-direction: row;
        align-items: center;
        margin-right: 10px;
        min-height: 25px;
        float: right;
        font-size: 0.8em;
        font-variant: small-caps;
        white-space: nowrap;

        img { margin-right: 3px; }

        span.normal { display: initial; }
        span.shrink { display: none; }

        &.shrink {
            span.normal { display: none; }
            span.shrink { display: initial; }
        }

        @media screen and (max-width: $breakpoint-desktop-s) {
            span.normal { display: none; }
            span.shrink { display: initial; }
        }
    }
}

.action-button {
    float: right;
}

</style>
