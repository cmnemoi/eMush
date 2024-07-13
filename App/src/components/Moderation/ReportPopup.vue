<template>
    <PopUp :is-open=reportDialogVisible @close="closeReportDialog()" class="report">
        <h1 class="title">{{ $t('moderation.report.popUpTitle') }}</h1>
        <p class="message" v-html="formatText($t('moderation.report.message'))"></p>
        <label v-if="selectPlayer"> {{ $t("moderation.report.player") }}:
            <select v-model="reportedPlayer" required>
                <option
                    value=""
                    selected
                    disabled
                >
                    {{ $t("moderation.report.choosePlayer") }}
                </option>
                <option v-for="(player, key) in reportablePlayers" :key="player.id" :value="player.id">
                    <img :src="characterBody(player.character.key)">
                    {{ $t(player.character.name) }}
                </option>
            </select>
        </label>
        <label>{{ $t("moderation.report.playerMessage") }}
            <textarea v-model="reportMessage" />
        </label>
        <label>{{ $t("moderation.sanctionReason") }}:
            <select v-model="reportReason" required>
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
                :disabled="reportReason == '' || (reportedPlayer == '' && selectPlayer)"
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
import { characterEnum } from "@/enums/character";


export default defineComponent ({
    name: 'ReportPopup',
    components: {
        PopUp
    },
    props: {
        reportDialogVisible: Boolean,
        selectPlayer: false,
    },
    data() {
        return {
            reportReason: "",
            reportMessage: "",
            reportedPlayer: "",
        };
    },
    computed: {
        ...mapGetters({
            user: 'auth/getUserInfo',
            reportablePlayers: 'moderation/getReportablePlayers'
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
            params.append('player', this.reportedPlayer);
            if (this.reportMessage) {
                params.append('adminMessage', this.reportMessage);
            }

            this.$emit("submitReport", params);
        },
        characterBody: function(character: string): string {
            const images = characterEnum[character];
            return images.body;
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
