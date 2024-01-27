<template>
    <div v-if="downloaded" :id="containerId" />
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
        const game = await import(/* webpackChunkName: "game" */ '@/game/game');
        this.downloaded = true;
        this.$nextTick(() => {
            this.gameInstance = game.launch(this.containerId, this.player);
        });
    }
});
</script>


<style lang="scss" scoped>
.placeholder {
    font-size: 2rem;
    font-family: 'Courier New', Courier, monospace;
}

::v-deep(canvas) {
    max-width: 100%;
}
</style>
