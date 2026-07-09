<template>
    <div class="card star-card" v-if="player">
        <div>
            <img class="avatar" :src="player.getCharacterPortrait()" :alt="player.getCharacterCompleteName()">
            <div class="dude">
                <img class="body" :src="player.getCharacterBody()" :alt="player.getCharacterCompleteName()">
                <div>
                    <h3 class="char-name">
                        {{ player.getCharacterCompleteName() }}
                    </h3>
                    <p>
                        <router-link class="pseudo" :to="{ name: 'TheEndUserPage', params: {userId: player.userId}}">
                            {{ player.username }}
                        </router-link>
                        <span class="likes">
                            {{ player.likes }} <img :src="getImgUrl('dislike.png')" alt="likes">
                        </span>
                    </p>
                </div>
                <Tippy class="report" tag="span" @click="$emit('openReportDialog', player)">
                    <img :src="getImgUrl('comms/alert.png')" alt="Report message">
                    <template #content>
                        <h1>{{ $t('moderation.report.name')}}</h1>
                        <p>{{ $t('moderation.report.description') }}</p>
                    </template>
                </Tippy>
            </div>
            <p class="epitaph" v-if="player.message">
                <PlayerMessage
                    :player="player"
                    :is-moderator="isModerator"
                    @open-hide-dialog="$emit('openHideDialog', $event)"
                    @open-edit-dialog="$emit('openEditDialog', $event)"
                    @open-report-dialog="$emit('openReportDialog', $event)"
                />
            </p>
            <div class="triumph">
                <p class="score" :class="[{'mush': player.isMush}]">
                    {{ player.score }}
                </p>
                <p class="death-cause">
                    <img :src="getImgUrl('ui_icons/dead.png')" alt="Dead" v-if="player.hasBadEndCause">
                    <img :src="getImgUrl('ready.png')" alt="Alive" v-else>
                    {{ $t("theEnd.endCause." + player.endCause) }}
                </p>
                <p class="nova">
                    <img :src="getImgUrl('nova/first.png')" alt="First"> {{ $t('theEnd.goldSuperNova') }}
                </p>
                <ul>
                    <li v-for="highlight in player.highlights.slice(0, 3)" :key="highlight">
                        <span v-html="formatText(highlight)" />
                    </li>
                </ul>
                <Tippy tag="button" @click="$emit('showHistory', player)">
                    <img :src="getImgUrl('notes.gif')" :alt="$t('theEnd.historyAndTriumph')">
                    <template #content>
                        <h1>{{ $t('theEnd.historyAndTriumph') }}</h1>
                        <p v-html="formatText($t('theEnd.historyAndTriumphDescription', { character: player.getCharacterCompleteName() }))" />
                    </template>
                </Tippy>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useStore } from "vuex";
import { ClosedPlayer } from "@/entities/ClosedPlayer";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import PlayerMessage from "@/components/Ranking/TheEndPage/PlayerMessage.vue";
import { Tippy } from "vue-tippy";

defineProps<{
    player: ClosedPlayer
}>();

defineEmits<{
    openHideDialog: [player: ClosedPlayer]
    openEditDialog: [player: ClosedPlayer]
    openReportDialog: [player: ClosedPlayer]
    showHistory: [player: ClosedPlayer]
}>();

const store = useStore();
const isModerator = computed(() => store.getters['auth/isModerator']);
</script>

<style lang="scss" scoped>
@use "@/assets/scss/theEndPage" as *;

p { margin: 0; }

.card { @extend %player-card; }

.avatar { @extend %player-avatar; }

.likes { @extend %player-likes; }

.dude { @extend %player-dude; }

.triumph { @extend %player-triumph; }

.star-card {
    margin: 0 0.5rem 2rem;
    align-self: stretch;
    min-height: 300px;
    & > div { padding: 1rem 1rem 1rem 15rem }

    &::after {
        border: 16px solid transparent;
        border-image: url("/src/assets/images/nova/star-border.png") 16 round;
        background: #283378;
        background-clip: padding-box;
        mask-image: linear-gradient(0deg, transparent 5%, rgba(0,0,0,.5) 40%, black 100%);
    }

    .epitaph {
        position: relative;
        margin: 1rem 1.2rem 1rem 0;
        padding: 1em 0.8em;
        border: 1px solid #5f67bf;
        background-color: #2d377a;
        overflow-wrap: break-word;
        font-style: italic;
        font-size: 1.3em;
        box-shadow: 0px 8px 6px -6px rgba(23, 68, 142, .6);

        &::before { //diamond pointer
            content:"";
            position: absolute;
            top: 6px;
            left: -7px;
            width: 14px;
            height: 14px;
            border: 1px solid #5f67bf;
            background-color: #2d377a;
            transform: rotate(-45deg);
            clip-path: polygon(0 0, 100% 0, 0 100%);
        }

        :deep(em) {
            color: $red;
        }
    }

    .triumph {
        .score {
            position: relative;
            width: 140px;
            min-height: 112px;
            margin: 0.3em;
            padding: 0.6em 0;
            font-family: $font-days-one;
            font-size: 3.2em;
            text-align: center;
            z-index: 2;
            background: url("/src/assets/images/nova/podium.png") no-repeat center bottom;

            &.mush { background-image:  url("/src/assets/images/nova/podium_mush.png") }
        }

        .nova { font-size: 1.1em; }

        ul li { margin: 0 0 1.2em 1em; }
    }
}

@media only screen and (max-width: 560px) {
    // ARBITRARY, NEEDED

    .star-card .triumph .score {
        width: 100%;
        margin: 0 auto 0.8em;
    }
}

@media only screen and (max-width: $breakpoint-mobile-l) {
    .star-card {
        & > div { padding: .6rem; }

        .avatar { top: 1.6em; }

        .dude { margin-bottom: 12rem; }

        .epitaph {
            margin-right: 0;
            margin-bottom: 0;
            overflow-wrap: break-word;
            font-size: 1.15em;
            padding: .6em;

            &::before {
                top: -7px;
                left: 14px;
                transform: rotate(45deg);
            }
        }
    }
}
</style>
