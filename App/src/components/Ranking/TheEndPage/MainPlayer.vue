<template>
    <div class="card guest-card">
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
            <div class="triumph">
                <p class="score" :class="[{'mush': player.isMush}]">
                    {{ player.score }}
                </p>
                <p class="death-cause">
                    <img :src="getImgUrl('ui_icons/dead.png')" alt="Dead" v-if="player.hasBadEndCause">
                    <img :src="getImgUrl('ready.png')" alt="Alive" v-else>
                    {{ $t('theEnd.endCause.' + player.endCause) }}
                </p>
                <p class="nova">
                    <img :src="getImgUrl(novae[rank].image)" :alt="novae[rank].alt"> {{ $t(novae[rank].description) }}
                </p>
                <p class="epitaph" v-if="player.message">
                    <PlayerMessage
                        :player="player"
                        :is-moderator="isModerator"
                        @open-hide-dialog="$emit('openHideDialog', $event)"
                        @open-edit-dialog="$emit('openEditDialog', $event)"
                        @open-report-dialog="$emit('openReportDialog', $event)"
                    />
                </p>
                <ul>
                    <li v-for="highlight in player.highlights.slice(0, 3)" :key="highlight">
                        <span v-html="formatContent(highlight)" />
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

const props = defineProps<{
    player: ClosedPlayer
    index: number
}>();

defineEmits<{
    openHideDialog: [player: ClosedPlayer]
    openEditDialog: [player: ClosedPlayer]
    openReportDialog: [player: ClosedPlayer]
    showHistory: [player: ClosedPlayer]
}>();

const store = useStore();
const rank = computed(() => Math.min(props.index, 3));
const isModerator = computed(() => store.getters['auth/isModerator']);
const novae = [
    { "image": 'nova/second.png', 'alt': 'Second', 'description': 'theEnd.silverSuperNova' },
    { "image": 'nova/third.png', 'alt': 'Third', 'description': 'theEnd.bronzeSuperNova' },
    { "image": 'nova/fourth.png', 'alt': 'Fourth', 'description': 'theEnd.discoveredSuperNova' },
    { "image": 'nova/fifth.png', 'alt': 'Fifth', 'description': 'theEnd.specialSuperNova' }
];
</script>

<style lang="scss" scoped>
@use "@/assets/scss/theEndPage" as *;

p { margin: 0; }

.card { @extend %player-card; }

.avatar { @extend %player-avatar; }

.likes { @extend %player-likes; }

.dude { @extend %player-dude; }

.triumph { @extend %player-triumph; }

.guest-card {
    width: 223px;
    min-height: 320px;
    margin: 0 1rem 3rem;

    &::after {
        border: 16px solid transparent;
        border-image: url("/src/assets/images/nova/guest-border.png") 16 round;
        background: #1d2d72;
        background-clip: padding-box;
        mask-image: linear-gradient(0deg, transparent 5%, rgba(0,0,0,.5) 65%, black 100%);
    }

    .avatar { top: 1.6em; }

    .triumph {
        margin-top: 8em;
        padding-bottom: 3em;
        font-size: .9rem;

        .score {
            font-size: 1.75rem;
            margin-right: 0.25em;
        }

        .death-cause { margin: 0; }

        .epitaph {
            overflow-wrap: break-word;
            font-style: italic;
            margin: 0.6em 0;
            clear: both;
        }
    }
}
</style>
