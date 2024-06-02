<template>
    <PopUp :is-open=reportDialogVisible @close="closeReportDialog()" v-if="player">
        <h1 class="title">{{ $t('reportPopup.title') }}</h1>
        <p class="message" v-html="formatText($t('moderation.report.message', { playerName: player.name }))"></p>
        <label>{{ $t("moderation.report.playerMessage") }}:
            <textarea v-model="moderationMessage" />
        </label>
        <label>{{ $t("moderation.sanctionReason") }}:
            <select v-model="moderationReason" required>
                <option
                    value=""
                    selected
                    disabled
                    hidden
                >
                    {{ $t("moderation.chooseReason") }}
                </option>
                <option v-for="reason in moderationReasons()" :key="reason.key" :value="reason.value">
                    {{ $t(reason.key) }}
                </option>
            </select>
        </label>
        <div class="actions">
            <button class="action-button" @click="submitReport()">{{ $t('moderation.report.submit') }}</button>
        </div>
    </PopUp>
</template>

<script lang="ts">
import { mapGetters } from "vuex";
import PopUp from "@/components/Utils/PopUp.vue";
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";
import { moderationReasons } from "@/enums/moderation_reason.enum";
import ModerationService from "@/services/moderation.service";

export default defineComponent ({
    name: 'ReportPopup',
    components: {
        PopUp
    },
    props: {
        reportDialogVisible: Boolean,
        player: { name: String, id: String } // the player to report
    },
    data() {
        return {
            reportReason: "",
            reportMessage: "",
        };
    },
    computed: {
        ...mapGetters({
            user: 'auth/getUserInfo',
        })
    },
    methods: {
        moderationReasons() {
            return moderationReasons;
        },
        closeReportDialog() {
            this.$emit("close");
        },
        submitReport() {
            const params = new URLSearchParams();

            params.append('reason', this.reportReason);
            if (this.moderationMessage) {
                params.append('adminMessage', this.reportMessage);
            }

            ModerationService.reportPlayer(this.player.id, params)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        formatText
    }
});
</script>

<style lang="scss" scoped>
.message {
    :deep(a) {
        color: $green;
        text-decoration: none;
        &:hover, &:focus, &:active { color: white; }
    }
}

.actions {
    flex-direction: row;
    align-self: center;

     button, a {
        min-width: 160px;
     }
}

</style>
