<template>
    <popUp :is-open="isOpen" @close="close">
        <div v-if="isReport()">
            <h1 class="title">{{ $t('moderation.sanctionDetail.report') }}</h1>
        </div>
        <div v-else>
            <h1 class="title">{{ $t('moderation.sanctionDetail.sanction') }}</h1>
        </div>
        <div class="sanction-details">
            <div class="row">
                <div class="cell"><strong>{{ $t('moderation.sanctionDetail.user') }}</strong> {{ moderationSanction.username }}</div>
                <div class="cell">
                    <div class="action-button router-button">
                        <router-link
                            :to="{ name: 'SanctionListPage', params: { username: moderationSanction.username, userId: moderationSanction.userId } }">
                            {{ $t('moderation.sanctionList') }}
                        </router-link>
                    </div>
                    <div class="action-button router-button">
                        <router-link
                            :to="{ name: 'ModerationUserListUserPage', params: { userId: moderationSanction.userId } }">
                            {{ $t('moderation.goToUserProfile') }}
                        </router-link>
                    </div>
                </div>
                <div class="cell">
                    <template v-if="moderationSanction.playerId">
                        <strong>{{ $t('moderation.sanctionDetail.player') }}</strong>
                        <img :src="getCharacterBodyFromKey(moderationSanction?.playerName)" alt="Character Image" style="max-width: 16px;" />
                        {{ getCharacterNameFromKey(moderationSanction?.playerName) }}
                    </template>
                </div>
                <div class="cell" >
                    <div class="action-button router-button" v-if="moderationSanction.playerId">
                        <router-link
                            :to="{ name: 'ModerationViewPlayerDetail', params: { playerId: moderationSanction.playerId } }">
                            {{ $t('moderation.goToPlayerDetails') }}
                        </router-link>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell double"><strong>{{ $t('moderation.sanctionDetail.sanctionType') }}</strong> {{ getSanctionTranslation(moderationSanction.moderationAction) }}</div>
                <div class="cell double"><strong>{{ $t('moderation.sanctionDetail.reason') }}</strong> {{ getReasonTranslation(moderationSanction.reason) }}</div>
            </div>
            <div class="row">
                <div class="cell triple"><strong>{{ $t('moderation.sanctionDetail.message') }}</strong> {{ moderationSanction.message }}</div>
                <div class="cell"><strong>{{ $t('moderation.sanctionDetail.author') }}</strong> {{ moderationSanction.authorName }}</div>
            </div>
            <div class="row">
                <div class="cell triple"><strong>{{ $t('moderation.sanctionDetail.evidence') }}</strong> {{ moderationSanction.sanctionEvidence.message }}</div>
            </div>
            <div class="row" :class="{ active: moderationSanction.isActive, inactive: !moderationSanction.isActive }">
                <div class="cell double">
                    <strong>{{ isReport() ? $t('moderation.sanctionDetail.reportDate') : $t('moderation.sanctionDetail.startDate') }}</strong>
                    {{ moderationSanction?.startDate.toLocaleDateString(currentLocale, { weekday: "long", year: "numeric", month: "long", day: "numeric", hour: "numeric" }) }}
                </div>
                <div class="cell double" v-if="!isReport()">
                    <div class="cell double">
                        <strong>{{ $t('moderation.sanctionDetail.endDate') }}</strong>
                        {{ moderationSanction?.endDate.toLocaleDateString(currentLocale, { weekday: "long", year: "numeric", month: "long", day: "numeric", hour: "numeric" }) }}
                    </div>
                </div>
            </div>
            <div class="row actions">
                <div class="cell double" v-if="isReport()">
                    <button class="action-button" @click="archiveReport(moderationSanction.id)">{{ $t('moderation.actions.archive') }}</button>
                    <button class="action-button" @click="closeReport(moderationSanction.id)">{{ $t('moderation.actions.close') }}</button>
                </div>
                <div class="cell double" v-else>
                    <button class="action-button" @click="suspendSanction(moderationSanction.id)">{{ $t('moderation.actions.suspend') }}</button>
                    <button class="action-button" @click="removeSanction(moderationSanction.id)">{{ $t('moderation.actions.delete') }}</button>
                </div>
            </div>
        </div>
    </popUp>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import popUp from "@/components/Utils/PopUp.vue";
import { ModerationSanction } from "@/entities/ModerationSanction";
import { moderationReasons, moderationSanctionTypes } from "@/enums/moderation_reason.enum";
import { characterEnum } from "@/enums/character";
import ModerationService from "@/services/moderation.service";

export default defineComponent({
    components: {
        popUp
    },
    props: {
        isOpen: Boolean,
        moderationSanction: ModerationSanction
    },
    emits: [
        "close",
        "update"
    ],
    computed: {
        currentLocale() {
            return this.$i18n.locale;
        }
    },
    methods: {
        close() {
            this.$emit('close');
        },
        removeSanction(sanctionId: number) {
            ModerationService.removeSanction(sanctionId)
                .then(() => {
                    this.$emit('update');
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        suspendSanction(sanctionId: number) {
            ModerationService.suspendSanction(sanctionId)
                .then(() => {
                    this.$emit('update');
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        archiveReport(sanctionId) {
            const params = new URLSearchParams();
            params.append('isAbusive', false);

            ModerationService.archiveReport(sanctionId, params)
                .then(() => {
                    this.$emit('update');
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        closeReport(sanctionId) {
            const params = new URLSearchParams();
            params.append('isAbusive', true);

            ModerationService.archiveReport(sanctionId, params)
                .then(() => {
                    this.$emit('update');
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        getReasonTranslation(reason) {
            const reasonObj = moderationReasons.find(item => item.value === reason);
            return reasonObj ? this.$t(reasonObj.key) : reason;
        },
        getSanctionTranslation(sanctionType) {
            const sanctionObj = moderationSanctionTypes.find(item => item.value === sanctionType);
            return sanctionObj ? this.$t(sanctionObj.key) : sanctionType;
        },
        getCharacterNameFromKey(characterKey: string) {
            return characterEnum[characterKey].name;
        },
        getCharacterBodyFromKey(characterKey: string) {
            return characterEnum[characterKey].body;
        },
        isReport() {
            return (
                this.moderationSanction?.moderationAction === 'report'
            );
        }
    }
});
</script>

<style lang="scss" scoped>

.sanction-details {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    width: 800px;
}

.row {
    display: contents; /* Use display: contents to make children use the grid layout directly */
}

.cell {
    padding: 5px 10px;
    display: flex;
}

.cell.double {
    grid-column: span 2; /* Span 2 columns */
}

.cell.triple {
    grid-column: span 3; /* Span 3 columns */
}

.cell.quad {
    grid-column: span 4; /* Span 4 columns */
}

.active {
    color: deeppink;
}

.inactive {
    color: skyblue;
}

.router-button a {
    text-decoration: none;
    color: white;
}

.action-button {
    @include button-style();
    margin: 0.2rem !important;
}
</style>
