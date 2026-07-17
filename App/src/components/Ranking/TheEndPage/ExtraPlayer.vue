<template>
    <div class="card extra-card">
        <div>
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
                <ReportButton class="report" @click="$emit('openReportDialog', player)" />
            </div>
            <div class="triumph">
                <p class="score mush" v-if="player.isMush">
                    {{ player.score }}
                </p>
                <p class="score" v-else>
                    {{ player.score }}
                </p>
                <p class="death-cause">
                    <img :src="getImgUrl('ui_icons/dead.png')" alt="Dead" v-if="player.hasBadEndCause">
                    <img :src="getImgUrl('ready.png')" alt="Alive" v-else>
                    {{ $t('theEnd.endCause.' + player.endCause) }}
                </p>
                <p class="nova">
                    <img :src="getImgUrl('nova/sixth.png')" alt="sixth"> {{ $t('theEnd.normalSuperNova') }}
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
                        <span v-html="formatText(highlight)" />
                    </li>
                </ul>
                <Tippy tag="button" @click="$emit('showHistory', player)">
                    <img :src="getImgUrl('notes.gif')" alt="Historique et Triomphe">
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
import ReportButton from "@/components/Moderation/ReportButton.vue";
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

.likes { @extend %player-likes; }

.dude { @extend %player-dude; }

.triumph { @extend %player-triumph; }

.extra-card {
    width: 223px;
    min-height: 130px;
    margin: 0 1rem 3rem;

    .triumph {
        margin-top: 1.5em;
        padding-bottom: 0;
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

    &::after {
        border: 1px solid #387fff;
        background: #2e408f;
        @include corner-bezel(16px, 0);
        mask-image: linear-gradient(0deg, transparent 30%, rgba(0,0,0,.5) 80%, black 100%);
    }
}
</style>
