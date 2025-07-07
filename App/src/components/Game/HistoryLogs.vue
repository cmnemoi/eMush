<template>
    <div class="history-logs">
        <span
            class="tab"
            :class="{ active: activeTab === 'action' }"
            @click="activeTab = 'action'"
        >
            {{ $t('deathpage.highlights') }}
        </span>
        <span
            class="tab"
            :class="{ active: activeTab === 'triumph' }"
            @click="activeTab = 'triumph'"
        >
            {{ $t('deathpage.triumphHistory') }}
        </span>
        <div class="logs" v-if="activeTab === 'triumph'">
            <div>
                <p v-for="gain in displayedTriumphGains" :key="gain">
                    <img :src="getImgUrl('ui_icons/point.png')" alt="dot"> <span v-html="formatText(gain)" />
                </p>
            </div>
            <div class="logs-actions">
                <a v-if="shouldShowReadMore" class="read-more-link" @click="toggleDisplayLimit">{{ $t('deathpage.readMore') }}</a>
                <a v-if="shouldShowReadLess" class="read-more-link" @click="toggleDisplayLimit">{{ $t('deathpage.readLess') }}</a>
            </div>
        </div>
        <div class="logs" v-if="activeTab === 'action'">
            <div>
                <p v-for="highlight in displayedPlayerHighlights" :key="highlight">
                    <img :src="getImgUrl('ui_icons/point.png')" alt="dot"> <span v-html="formatText(highlight)" />
                </p>
            </div>
            <div class="logs-actions">
                <a v-if="shouldShowReadMoreAction" class="read-more-link" @click="toggleDisplayLimit">{{ $t('deathpage.readMore') }}</a>
                <a v-if="shouldShowReadLessAction" class="read-more-link" @click="toggleDisplayLimit">{{ $t('deathpage.readLess') }}</a>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue';
import { getImgUrl } from '@/utils/getImgUrl';
import { formatText } from '@/utils/formatText';
const props = defineProps<{ triumphGains: string[], playerHighlights: string[] }>();

// refs
const showAllTriumphGains = ref(false);
const showAllPlayerHighlights = ref(false);
const activeTab = ref('triumph');

// computed
const displayedTriumphGains = computed(() =>
    showAllTriumphGains.value ? props.triumphGains : props.triumphGains.slice(0, displayLimit)
);
const displayedPlayerHighlights = computed(() =>
    showAllPlayerHighlights.value ? props.playerHighlights : props.playerHighlights.slice(0, displayLimit)
);
const shouldShowReadMore = computed(() =>
    !showAllTriumphGains.value && props.triumphGains.length > displayLimit
);
const shouldShowReadLess = computed(() =>
    showAllTriumphGains.value && props.triumphGains.length > displayLimit
);
const shouldShowReadMoreAction = computed(() =>
    !showAllPlayerHighlights.value && props.playerHighlights.length > displayLimit
);
const shouldShowReadLessAction = computed(() =>
    showAllPlayerHighlights.value && props.playerHighlights.length > displayLimit
);

// methods
const toggleDisplayLimit = () => {
    showAllTriumphGains.value = !showAllTriumphGains.value;
    showAllPlayerHighlights.value = !showAllPlayerHighlights.value;
};

// data
const displayLimit = 3;
</script>

<style lang="scss" scoped>
.history-logs {
    display: block;
}

.tab {
    display: block;
    float: left;
    cursor: pointer;
    font-size: .86em;
    opacity: .6;
    padding: 5px 15px;
    border-radius: 5px 5px 0 0;

    &:hover, &:focus, &:active, &.active { opacity: 1; }

    &.active {
        background: #222b6b;
        border-top: 1px solid #3d4dbf;
    }
}

.logs {
    width: 100%;
    border-top: 1px solid #3d4dbf;
    font-size: .8em;
    letter-spacing: .05em;
    padding: 1em;
    display: flex;
    flex-direction: column;
    position: relative;

    p { margin: .5em 0; }

    .logs-actions {
        align-self: flex-end;
        margin-top: 0.5em;
    }
}

.read-more-link {
    color: $deepGreen;
    cursor: pointer;
    text-decoration: underline;
    display: inline-block;
    font-size: 0.9em;
    margin-left: 1em;
}
</style>
