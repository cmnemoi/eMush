<template>
    <PopUp :is-open=reportDialogVisible @close="closeReportDialog()" v-if="player" class="report">
        <h1 class="title">{{ $t('reportPopup.title') }}</h1>
        <p class="message" v-html="formatText($t('moderation.report.message', { playerName: player.name }))"></p>
        <label>{{ $t("moderation.report.playerMessage") }}:
            <textarea v-model="moderationMessage" />
        </label>
        <label>{{ $t("moderation.sanctionReason") }}:
            <select v-model="reason" required>
                <option
                    value=""
                    selected
                    disabled
                >
                    {{ $t("moderation.chooseReason") }}
                </option>
                <option v-for="reason in moderationReasons()" :key="reason.key" :value="reason.value">
                    {{ $t(reason.key) }}
                </option>
            </select>
        </label>
        <div class="actions">
            <button
                class="action-button"
                @click="submitReport()"
                :disabled="reason == ''"
            >
                {{ $t('moderation.report.submit') }}
            </button>
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
            reason: ""
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
.report {
    color: white;

    h1 {
        margin-top: 0.6em;
        font-size: 1.4em;
        line-height: 1.2em;
    }

    a {
        color: $green;
        text-decoration: none;
        &:hover, &:focus, &:active { color: white; }
    }

    p, label {
        padding: 0.6em 0;
        margin-bottom: 0.2em;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    textarea {
        width: 100%;
        height: 4em;
        max-height: 10em;
        resize: vertical;
    }

    select {
        width: 100%;
    }
}

.actions {
    flex-direction: row;
    align-self: center;
    padding: 0.6em;

     button, a {
        min-width: 160px;
        padding-top: .3em;
        padding-bottom: .3em;
     }
}

.modal-background {
    position: absolute;

    :deep(.modal-box) {
        min-width: auto;
        width: calc(100% - 2em);
        padding-bottom: 0.6em;
    }
}

</style>
