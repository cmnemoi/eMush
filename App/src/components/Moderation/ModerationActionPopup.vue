<template>
    <PopUp :is-open=moderationDialogVisible @close="closeModerationDialog()">
        <h2>{{ $t(action.key) }}</h2>
        <label>{{ $t("moderation.sanctionReason") }}:
            <select v-model="moderationReason">
                <option v-for="reason in moderationReasons()" :key="reason.key" :value="reason.value">{{ $t(reason.key) }}</option>
            </select>
        </label>
        <label>{{ $t("moderation.adminMessage") }}:
            <textarea v-model="moderationMessage"></textarea>
        </label>
        <label v-if="showDateOptions">{{ $t("moderation.startDate") }}:
            <input type="date" v-model="moderationStartDate" />
        </label>
        <label v-if="showDateOptions">{{ $t("moderation.duration") }}:
            <select v-model="moderationDuration">
                <option value="">{{ $t('moderation.durations.permanent') }}</option>
                <option v-for="duration in sanctionDuration()" :value="duration.value" :key="duration.key">{{ $t(duration.key) }}</option>
            </select>
        </label>
        <div class="actions">
            <button class="action-button" @click="submitSanction">{{ $t("moderation.confirmSanction") }}</button>
        </div>
    </PopUp>
</template>

<script>
import PopUp from "@/components/Utils/PopUp.vue";
import { moderationReasons, sanctionDuration } from "@/enums/moderation_reason.enum";

export default {
    components: { PopUp },
    props: {
        moderationDialogVisible: Boolean,
        action: { key: String, value: String } // moderation action that opened the pop-up
    },
    data() {
        return {
            moderationReason: "",
            moderationMessage: "",
            moderationStartDate: "",
            moderationDuration: ""
        };
    },
    computed: {
        showDateOptions() {
            // display the starting date and duration of the sanction only for ban and warning
            return this.action.value === "ban_user" || this.action.value === "warning" || this.action.value === "quarantine_ban";
        }
    },
    methods: {
        moderationReasons() {
            return moderationReasons;
        },
        sanctionDuration() {
            return sanctionDuration;
        },
        closeModerationDialog() {
            this.$emit("close");
        },
        submitSanction() {
            const params = new URLSearchParams();

            params.append('reason', this.moderationReason);
            if (this.moderationMessage) {
                params.append('adminMessage', this.moderationMessage);
            }
            if (this.moderationStartDate) {
                params.append('startDate', this.moderationStartDate);
            }
            if (this.moderationDuration) {
                params.append('duration', this.moderationDuration);
            }

            this.$emit("submitSanction", params);
        }
    }
};
</script>
