<template>
    <ReportPopup
        :report-dialog-visible="reportPopupVisible"
        :select-player="true"
        @close=closeReportDialog
        @submit-report=submitComplaint
    />
    <section
        v-if="roomLog"
        :class="[`log ${roomLog.visibility}`, { unread: roomLog.isUnread, read: !roomLog.isUnread }]"
        @mouseover="read(roomLog)"
    >
        <p class='text-log'>
            <span v-html="formatText(roomLog.message)"></span>
            <span class="room" v-if="roomLog?.place"> - {{ roomLog.place }}</span>
            <span class="timestamp">{{ roomLog?.date }}</span>
        </p>
        <div class="actions" v-if="roomLogChannel.id" @click.stop>
            <Tippy tag="span" @click="openReportDialog">
                <img :src="getImgUrl('comms/alert.png')" alt="Report message">
                <template #content>
                    <h1>{{ $t('moderation.report.name')}}</h1>
                    <p>{{ $t('moderation.report.description') }}</p>
                </template>
            </Tippy>
        </div>
    </section>
</template>

<script lang="ts">
import { formatText } from "@/utils/formatText";
import { RoomLog } from "@/entities/RoomLog";
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";
import { getImgUrl } from "@/utils/getImgUrl";
import ReportPopup from "@/components/Moderation/ReportPopup.vue";
import { Tippy } from "vue-tippy";

export default defineComponent ({
    name: "Log",
    components: { Tippy, ReportPopup },
    data() {
        return {
            reportPopupVisible: false
        };
    },
    computed: {
        ...mapGetters({
            isReadingLog: "communication/readMessageMutex",
            roomLogChannel: 'communication/currentChannel'
        })
    },
    props: {
        roomLog: {
            type: RoomLog,
            required: true
        }
    },
    methods: {
        ...mapActions({
            acquireReadLogMutex: "communication/acquireReadMessageMutex",
            releaseReadLogMutex: "communication/releaseReadMessageMutex",
            readRoomLog: "communication/readRoomLog",
            loadReportablePlayers: 'moderation/loadReportablePlayers',
            reportRoomLog: 'moderation/reportRoomLog'
        }),
        formatText,
        getImgUrl,
        async read(roomLog: RoomLog) {
            if (!this.isReadingLog && roomLog.isUnread) {
                await this.acquireReadLogMutex();
                await this.readRoomLog(roomLog);
                await this.releaseReadLogMutex();
            }
        },
        openReportDialog() {
            this.reportPopupVisible = true;
            this.loadReportablePlayers();
        },
        closeReportDialog() {
            this.reportPopupVisible = false;
        },
        async submitComplaint(params: URLSearchParams) {
            await this.reportRoomLog({ roomLogId: this.roomLog.id, params });
            this.reportPopupVisible = false;
        }
    }
});
</script>

<style lang="scss" scoped>

.log {
    position: relative;
    padding: 4px 5px;
    margin: 1px 0;
    border-bottom: 1px solid rgb(170, 212, 229);

    &::v-deep(p:not(.timestamp) em)  { color: $red; }

    &.new, &.unread { // unread messages styling
        border-left: 2px solid #ea9104;
        padding-left: 8px;

        &::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: -6px;
            min-height: 11px;
            width: 11px;
            background: transparent url('/src/assets/images/comms/thinklinked.png') center no-repeat;
        }
    }

    &.private {
        color: #98388a;
        font-style: italic;
    }

    &.covert,
    &.secret {
        border-radius: 3px;
        background: #88def8;
        font-style: italic;
        border: none;
    }

    &.revealed {
        background: #e29ec3;
        border: 1px solid #ff3f58 !important;
        font-style: normal;
        border-radius: 3px;
    }

    &.personnal,
    &.covert,
    &.secret,
    &.revealed {
        .timestamp::before {
            content: "";
            display: inline-block;
            margin-right: 4px;
            vertical-align: middle;
        }
    }

    &.personnal .timestamp::before {
        width: 16px;
        height: 16px;
        background: url('/src/assets/images/comms/personnal.png') center no-repeat;
    }

    &.covert .timestamp::before {
        width: 16px;
        height: 16px;
        background: url('/src/assets/images/comms/covert.png') center no-repeat;
    }

    &.secret .timestamp::before {
        width: 16px;
        height: 15px;
        background: url('/src/assets/images/comms/discrete.png') center no-repeat;
    }

    &.revealed .timestamp::before {
        width: 20px;
        height: 16px;
        background: url('/src/assets/images/comms/spotted.png') center no-repeat;
    }

    &.read {
        border-left: 0 solid transparent;
        transition: 0.1s ease-in-out border-left;
    }
}

.actions { //buttons styling
    $delay-hide: 0.15s;

    position: absolute;
    visibility: hidden;
    opacity: 0;
    z-index: 5;
    right: 3px;
    bottom: -2px;
    height: 18px;
    transition: visibility 0s $delay-hide, opacity $delay-hide 0s, bottom $delay-hide 0s;
}

.log:hover {
    .actions {
        $delay-show: 0.3s;

        visibility: visible;
        opacity: 1;
        bottom: 7px;
        transition: visibility 0s $delay-show, opacity 0.15s $delay-show, bottom 0.15s $delay-show;
    }
}

.text-log {
    margin: 0;
    font-size: 0.92em;
}

.room { font-variant: small-caps; }

</style>
