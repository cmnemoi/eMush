<template>
    <div v-if="downloaded" ref="container" />
    <div v-else class="placeholder">
        {{ $t('downloading') }}
    </div>
</template>


<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Player } from '@/entities/Player';

interface Props {
    player: Player;
}

const props = defineProps<Props>();

const downloaded = ref(false);
const gameInstance = ref<Phaser.Game | null>(null);
const container = ref<HTMLDivElement>();

onMounted(async () => {
    if (gameInstance.value) return;

    const phaserScene = await import('@/game/game');
    downloaded.value = true;

    // Make sure the container is ready
    await nextTick();

    // Remove old scene
    const sceneContainer = container.value;
    if (!sceneContainer) return;
    while (sceneContainer.firstChild) sceneContainer.removeChild(sceneContainer.firstChild);

    // Launch new scene
    gameInstance.value = phaserScene.launch(sceneContainer, props.player);
});
</script>


<style lang="scss" scoped>
.placeholder {
    font-size: 2rem;
    font-family: 'Courier New', Courier, monospace;
}

:deep(canvas) {
    max-width: 100%;

    @media screen and (max-width: 450px) {
        image-rendering: smooth !important;
    }
}
</style>
