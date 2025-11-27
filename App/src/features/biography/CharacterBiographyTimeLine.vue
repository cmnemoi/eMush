<template>
    <div class="timeline">
        <div v-for="(item, index) in bio" class="timeline-item" :key="`${index}`">
            <div class="timeline-date">{{ item.date }}</div>
            <div class="timeline-entry" v-html="formatText(item.entry)"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { formatText } from '@/utils/formatText';
const { bio } = defineProps<{ bio: { date: string; entry: string }[] }>();
</script>

<style scoped>
.timeline-item {
    display: grid;
    grid-template-columns: 70px 1fr;
    padding-bottom: 0.5rem;
    border-bottom: 1px dotted #4d64a1;
}

.timeline-item:last-child {
    border-bottom: none !important;
}

.timeline-date {
    color: #35fafc;
    font-family: monospace;
    font-size: 24px;
    font-weight: bold;
    width: 70px;
    text-shadow: 0 0 3px #35fafc;
}

.timeline-entry {
    color: #b1c5f9;
    font-style: italic;
    padding: 5px;
    line-height: 1.4;
    display: block;
}

.timeline-entry :deep(strong) {
    color: #ff4f79;
}
</style>
