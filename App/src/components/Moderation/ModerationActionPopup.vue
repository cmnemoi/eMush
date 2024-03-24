<template>
    <PopUp :is-open=moderationDialogVisible @close="closeModerationDialog()">
        <h2>{{ $t("moderation.dialogTitle") }}</h2>
        <label>{{ $t("moderation.reason") }}:
            <select v-model="moderationReason">
                <option v-for="reason in moderationReasonOptions" :key="reason">{{ reason }}</option>
            </select>
        </label>
        <label>{{ $t("moderation.message") }}:
            <textarea v-model="moderationMessage"></textarea>
        </label>
        <label v-if="showDateOptions">{{ $t("moderation.startDate") }}:
            <input type="date" v-model="moderationStartDate" />
        </label>
        <label v-if="showDateOptions">{{ $t("moderation.duration") }}:
            <select v-model="moderationDuration">
                <option v-for="duration in moderationDurationOptions" :key="duration.key" :value="duration.value">{{ duration.key }}</option>
            </select>
        </label>
        <div class="actions">
            <button class="action-button" @click="submitSanction">{{ $t("moderation.confirmSanction") }}</button>
        </div>
    </PopUp>
</template>

<script>
import {moderationReasons, sanctionDuration} from "@/enums/moderation_reason.enum";
import PopUp from "@/components/Utils/PopUp.vue";

export default {
    components: { PopUp },
    props: {
        moderationDialogVisible: Boolean,
        action: String, // moderation action that opened the pop-up
    },
    data() {
        return {
            moderationReasonOptions: moderationReasons,
            moderationDurationOptions: sanctionDuration,
            moderationReason: "",
            moderationMessage: "",
            moderationStartDate: "",
            moderationDuration: "",
        };
    },
    computed: {
        showDateOptions() {
            // display the starting date and duration of the sanction only for ban and warning
            return this.action === "ban" || this.action === "warning" || this.action === "quarantine_ban";
        },
    },
    methods: {
        closeModerationDialog() {
            this.$emit("close");
        },
        submitSanction() {
            const params = new URLSearchParams();

            params.append('reason', this.moderationReason);
            if (this.moderationMessage) {
                params.append('message', this.moderationMessage);
            }
            if (this.moderationStartDate) {
                params.append('startDate', this.moderationStartDate);
            }
            if (this.moderationDuration) {
                params.append('duration', this.moderationDuration);
            }

            this.$emit("submitSanction", params);
        },
    },
};
</script>