<template>
    <section class="unit" v-if="generalAnnouncement">
        <div class="banner">
            <span><img :src="getImgUrl('infoalert.png')"> {{ announcement.title }} <img :src="getImgUrl('infoalert.png')"></span>
        </div>
        <div class="announcement" :key="generalAnnouncement.comManager.key">
            <div class="message">
                <div class="char-portrait">
                    <img :src="getImgUrl(`char/body/${generalAnnouncement.comManager.key}.png`)">
                </div>
                <p>
                    <span class="author">{{ generalAnnouncement.comManager.name }} :</span>
                    <span class="announcement-text" v-html="formatText(generalAnnouncement.announcement)"></span>
                    <span class="timestamp">{{ generalAnnouncement.date }}</span>
                    <ReportButton class="report-button" @click="$emit('reportAnnouncement', generalAnnouncement)" />
                </p>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Announcement, ComManagerAnnouncementElement } from "@/entities/Channel";
import { computed } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import ReportButton from "@/components/Moderation/ReportButton.vue";

const props = defineProps<{
    announcement: Announcement
}>();

defineEmits<{
    reportAnnouncement: [announcement: ComManagerAnnouncementElement]
}>();

const generalAnnouncement = computed((): ComManagerAnnouncementElement | undefined => {
    return props.announcement?.element;
});
</script>

<style lang="scss" scoped>
.message {
    @extend %comm-message-bubble;
}

.report-button {
    float: right;
}
</style>
