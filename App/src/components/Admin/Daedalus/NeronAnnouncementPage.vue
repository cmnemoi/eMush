<template>
    <form>
        <textarea v-model="announcement"/>
    </form>
    <button class="action-button" @click="sendNeronAnnouncement">
        {{ $t('admin.daedalus.sendNeronAnnouncementToAllDaedaluses') }}
    </button>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import AdminService from "@/services/admin.service";

export default defineComponent ({
    name: "AdminNeronAnnouncement",
    methods: {
        sendNeronAnnouncement(): void {
            if (!this.announcement) {
                return;
            }
            AdminService.sendNeronAnnouncementToAllDaedaluses(this.announcement);
        }
    },
    data() {
        return {
            announcement: ""
        };
    }
});
</script>

<style  lang="scss" scoped>

form {
    background: #222b6b;
    border-collapse: collapse;
    border: thin solid #1B2256;
    margin-bottom: 1%;

    textarea {
        background: transparent;
        border: thin solid rgba(255, 255, 255, .25);
        color: #fff;
        line-height: 1.4em;
        padding: 0.4em;
        width: 100%;
        height: 5em;
        resize: vertical;
    }
}
</style>
