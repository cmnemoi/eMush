<template>
    <div v-if="downloaded" ref="container" />
    <div v-else class="placeholder">
        {{ $t('downloading') }}
    </div>
</template>


<script lang="ts">
import { Player } from "@/entities/Player";
import { defineComponent } from "vue";

interface PhaserShipState {
    downloaded: boolean,
    gameInstance: null | Phaser.Game,
    containerId: string
}

export default defineComponent ({
    name: 'Game',
    data(): PhaserShipState {
        return {
            downloaded: false,
            gameInstance: null,
            containerId: 'game-container'
        };
    },
    props: {
        player: {
            type: Player,
            required: true
        }
    },
    async mounted() {
        if (this.gameInstance) return;

        const phaserScene = await import("@/game/game");
        this.downloaded = true;

        // Make sure the container is ready
        await this.$nextTick();

        // Remove old scene
        const sceneContainer = this.$refs.container as HTMLDivElement | undefined;
        if (!sceneContainer) return;
        while (sceneContainer.firstChild) sceneContainer.removeChild(sceneContainer.firstChild);

        // Launch new scene
        this.gameInstance = phaserScene.launch(sceneContainer, this.player);
    },
    beforeUnmount() {
        // Delete scene before destroying the container
        if (this.gameInstance !== null) {
            this.gameInstance.destroy(true);
            this.gameInstance = null;
        }
    }
});
</script>


<style lang="scss" scoped>
.placeholder {
    font-size: 2rem;
    font-family: 'Courier New', Courier, monospace;
}

:deep(canvas) {
    max-width: 100%;
}
</style>
