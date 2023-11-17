<template>
    <h1>{{ $t('admin.neronAnnouncement.sendNeronAnnouncement') }}</h1>
    <div class="flex-row">
        <table class="form">
            <tbody>
                <tr>
                    <th>{{ $t('admin.neronAnnouncement.frenchAnnouncement') }}</th>
                    <td><textarea v-model="frenchAnnouncement" /></td>
                </tr>
                <tr>
                    <th>{{ $t('admin.neronAnnouncement.englishAnnouncement') }}</th>
                    <td><textarea v-model="englishAnnouncement" /></td>
                </tr>
            </tbody>
        </table>
        <div class="templates">
            <h3>Templates:</h3>
            <button class="action-button" @click="getTemplateByName('destroyAndUpdate')">
                {{ $t('admin.neronAnnouncement.destroyAndUpdate') }}
            </button>
            <button class="action-button" @click="getTemplateByName('maintenance')">
                {{ $t('admin.neronAnnouncement.maintenance') }}
            </button>
        </div>
    </div>
    <button :class="'action-button ' + (areAnnouncementsFilled() ? '' : 'disabled')" @click="sendNeronAnnouncements">
        {{ $t('admin.neronAnnouncement.sendNeronAnnouncementToAllDaedaluses') }}
    </button>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { GameLocales } from "@/i18n";
import AdminService from "@/services/admin.service";

export default defineComponent ({
    name: "AdminNeronAnnouncement",
    methods: {
        areAnnouncementsFilled(): boolean {
            return this.frenchAnnouncement != "" && this.englishAnnouncement !== "";
        },
        getTemplateByName(name: string): void {
            const selectedLocale = this.$i18n.locale;

            this.$i18n.locale = GameLocales.EN;
            this.englishAnnouncement = this.$t(`admin.neronAnnouncement.${name}Content`) as string;

            this.$i18n.locale = GameLocales.FR;
            this.frenchAnnouncement = this.$t(`admin.neronAnnouncement.${name}Content`) as string;

            this.$i18n.locale = selectedLocale;
        },
        sendNeronAnnouncements(): void {
            if (!this.frenchAnnouncement || !this.englishAnnouncement) {
                return;
            }

            AdminService.sendNeronAnnouncementToAllDaedalusesByLanguage(this.frenchAnnouncement, GameLocales.FR);
            AdminService.sendNeronAnnouncementToAllDaedalusesByLanguage(this.englishAnnouncement, GameLocales.EN);
        }
    },
    data() {
        return {
            frenchAnnouncement: "",
            englishAnnouncement: "",
        };
    }
});
</script>

<style  lang="scss" scoped>

table {
    background: #222b6b;
    border-collapse: collapse;
    border: thin solid #1B2256;
    margin-bottom: 1%;
    width: 75%;

    tbody tr {
        border-top: 1px solid rgba(0,0,0,0.2);
    }

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

    th, td {
        padding: 1em 0.5em 1em 1.2em;
        vertical-align: middle;
        &::v-deep(a), &::v-deep(button) {
            @include button-style();
            width: fit-content;
            padding: 2px 15px 4px;
        }
    }

    th {
        // opacity: .75;
        letter-spacing: .05em;
        text-align: left;
        font-weight: bold;
        width: 20%;
    }

}
</style>
