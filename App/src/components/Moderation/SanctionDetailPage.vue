<template>
    <popUp :is-open="isOpen" @close="close">
        <div>
            <h1 class="title">
                {{ $t(`moderation.sanctionDetail.${sanctionType}.title`) }}
            </h1>
        </div>

        <div class="actors">
            <div>
                <h2 class="actor-title">{{ $t('moderation.sanctionDetail.user') }}</h2>
                <div class="actor-details">
                    <span>
                            <strong>{{ $t('moderation.sanctionDetail.username') }}</strong>
                            {{ moderationSanction.user.username }}
                    </span>
                    <span v-if="moderationSanction.user.playerName">
                            <strong>{{ $t('moderation.sanctionDetail.player') }}</strong>
                            <img :src="characterEnum[moderationSanction.user.playerName]?.head">
                            {{ characterEnum[moderationSanction.user.playerName]?.name }}
                    </span>

                    <div class="actor-actions">
                        <router-link
                            v-if="moderationSanction.user.playerId"
                            :to="{ name: 'ModerationViewPlayerDetail', params: { playerId: moderationSanction.user.playerId } }"
                            class="action-button"
                        >
                            {{ $t('moderation.goToPlayerDetails') }}
                        </router-link>
                        <router-link
                            :to="{ name: 'ModerationUserListUserPage', params: { userId: moderationSanction.user.id } }"
                            class="action-button"
                        >
                            {{ $t('moderation.goToUserProfile') }}
                        </router-link>
                        <router-link
                            :to="{ name: 'SanctionListPage', params: { username: moderationSanction.user.username, userId: moderationSanction.user.id } }"
                            class="action-button"
                        >
                            {{ $t('moderation.sanctionList') }}
                        </router-link>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="actor-title">{{ $t(`moderation.sanctionDetail.${sanctionType}.author`)}}</h2>
                <div class="actor-details">
                    <span>
                        <strong>{{ $t('moderation.sanctionDetail.username') }}</strong>
                        {{ moderationSanction.author.username }}
                    </span>
                    <span v-if="moderationSanction.author.playerName">
                        <strong>{{ $t('moderation.sanctionDetail.player') }}</strong>
                        <img :src="characterEnum[moderationSanction.author.playerName]?.head">
                        {{ characterEnum[moderationSanction.author.playerName]?.name }}
                    </span>

                    <div class="actor-actions">
                        <router-link
                            v-if="moderationSanction.author.playerId"
                            :to="{ name: 'ModerationViewPlayerDetail', params: { playerId: moderationSanction.author.playerId } }"
                            class="action-button"
                        >
                            {{ $t('moderation.goToPlayerDetails') }}
                        </router-link>
                        <router-link
                            :to="{ name: 'ModerationUserListUserPage', params: { userId: moderationSanction.author.id } }"
                            class="action-button"
                        >
                            {{ $t('moderation.goToUserProfile') }}
                        </router-link>
                    </div>
                </div>
            </div>
        </div>

        <div class="sanction">
            <div class="sanction-details">
                <span>
                    <strong>{{ $t('moderation.sanctionDetail.sanctionType') }}</strong>
                    {{ $t(`moderation.type.${moderationSanction.moderationAction}`) }}
                </span>
                <span>
                    <strong>{{ $t('moderation.sanctionDetail.reason') }}</strong>
                    {{ $t(`moderation.reason.${moderationSanction.reason}`) }}
                </span>
                <span v-if="moderationSanction.sanctionEvidence.className">
                    <strong>{{ $t('moderation.sanctionDetail.context') }}</strong>
                    {{ $t(`moderation.context.${moderationSanction.sanctionEvidence.className}`) }}
                </span>
            </div>
            <div class="sanction-evidences">
                <div v-if="moderationSanction.sanctionEvidence.message" class="sanction-evidence">
                    <strong>{{ $t('moderation.sanctionDetail.evidence') }}</strong>
                    <div class="sanction-message">{{ moderationSanction.sanctionEvidence.message }}</div>
                    <button class="action-button" @click="goToReportEvidence(moderationSanction)">
                        {{ $t('moderation.report.seeContext') }}
                    </button>
                </div>
                <div class="sanction-evidence">
                    <strong>{{ $t(`moderation.sanctionDetail.${sanctionType}.message`) }}</strong>
                    <div class="sanction-message">{{ moderationSanction.message }}</div>
                </div>
            </div>
            <div class="sanction-details">
                <span :class="moderationSanction.isActive ? 'active' : 'inactive'">
                    <strong>{{ $t(`moderation.sanctionDetail.${sanctionType}.startDate`) }}</strong>
                    {{ formatModerationDate(moderationSanction.startDate, currentLocale, $t) }}

                </span>
                <span v-if="sanctionType !== 'report'" :class="moderationSanction.isActive ? 'active' : 'inactive'">
                    <strong>{{ $t(`moderation.sanctionDetail.${sanctionType}.endDate`) }}</strong>
                    {{ formatModerationDate(moderationSanction.endDate, currentLocale, $t) }}
                </span>
            </div>
            <div class="cell double" v-if="sanctionType === 'report'">
                <button class="action-button" @click="archiveReport(moderationSanction.id)">{{ $t('moderation.actions.archive') }}</button>
                <button class="action-button" @click="closeReport(moderationSanction.id)">{{ $t('moderation.actions.close') }}</button>
            </div>
            <div class="cell double" v-else>
                <button class="action-button" @click="suspendSanction(moderationSanction.id)">{{ $t('moderation.actions.suspend') }}</button>
                <button class="action-button" @click="removeSanction(moderationSanction.id)">{{ $t('moderation.actions.delete') }}</button>
            </div>
        </div>
    </popUp>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import popUp from "@/components/Utils/PopUp.vue";
import { ModerationSanction } from "@/entities/ModerationSanction";
import { formatModerationDate } from "@/utils/moderation/formatModerationDate";
import { archiveReport as archiveReportRequest, suspendSanction as suspendSanctionRequest, removeSanction as removeSanctionRequest } from "@/utils/moderation/moderationSanctionActions";
import { goToReportEvidence } from "@/utils/moderation/sanctionEvidenceNavigation";
import {characterEnum} from "@/enums/character";
import {moderation} from "@/store/moderation.module";

export default defineComponent({
    components: {
        popUp,
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
        moderation() {
            return moderation
        },
        characterEnum() {
            return characterEnum
        },
        currentLocale() {
            return this.$i18n.locale;
        },
        sanctionType() {
            const reportActions = ['report', 'report_processed', 'report_abusive'];
            return (
                reportActions.includes(this.moderationSanction.moderationAction) ? 'report' : 'sanction'
            );
        }
    },
    methods: {
        formatModerationDate,
        goToReportEvidence,
        close() {
            this.$emit('close');
        },
        removeSanction(sanctionId: number) {
            removeSanctionRequest(sanctionId)
                .then(() => {
                    this.$emit('update');
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        suspendSanction(sanctionId: number) {
            suspendSanctionRequest(sanctionId)
                .then(() => {
                    this.$emit('update');
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        archiveReport(sanctionId: number) {
            archiveReportRequest(sanctionId, false)
                .then(() => {
                    this.$emit('update');
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        closeReport(sanctionId: number) {
            archiveReportRequest(sanctionId, true)
                .then(() => {
                    this.$emit('update');
                })
                .catch((error) => {
                    console.error(error);
                });
        }
    }
});
</script>

<style lang="scss" scoped>
.action-button {
    @include button-style();
}

.title {
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.75);
}

.actors {
    flex-direction: row;
    flex-wrap: wrap;
    column-gap: 50px;
    row-gap: 20px;
    justify-content: center;
    padding: 10px 0 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.75);

    .actor-title { margin: 0 0 5px; }

    .actor-actions { gap: 5px; }

    .actor-details{
        gap: 10px;
        min-width: 200px;

        span {
            display: inline-flex;
            gap: 2px;
        }
    }
}

.sanction {
    padding: 10px 0 15px;
    gap: 10px;

    .sanction-details, .sanction-evidences {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        column-gap: 30px;
        row-gap: 10px;

        .active { color: deeppink; }

        .inactive { color: skyblue; }

        .sanction-evidence {
            width: 48%;
            min-width: 250px;

            .sanction-message {
                height: 100%;
                border: 1px solid #5f67bf;
                margin-top: 5px;
                padding: 5px 10px;
                color: white;
                font-style: italic;
                background: none;
            }
        }
    }
}
</style>
