<template>
    <div class="history-logs">
        <span class="tab"> {{ $t('deathpage.highlights') }} </span>
        <span class="tab active"> {{ $t('deathpage.triumphHistory') }} </span>
        <div class="logs">
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
    </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue';
import { getImgUrl } from '@/utils/getImgUrl';
import { formatText } from '@/utils/formatText';
const props = defineProps<{ triumphGains: string[] }>();

// refs
const showAllTriumphGains = ref(false);

// computed
const displayedTriumphGains = computed(() =>
    showAllTriumphGains.value ? props.triumphGains : props.triumphGains.slice(0, displayLimit)
);
const shouldShowReadMore = computed(() =>
    !showAllTriumphGains.value && props.triumphGains.length > displayLimit
);
const shouldShowReadLess = computed(() =>
    showAllTriumphGains.value && props.triumphGains.length > displayLimit
);

// methods
const toggleDisplayLimit = () => {
    showAllTriumphGains.value = !showAllTriumphGains.value;
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
