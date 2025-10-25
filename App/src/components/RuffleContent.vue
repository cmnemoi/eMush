<template>
    <div ref="containerRef" />
</template>

<script lang="ts">
import { defineComponent, onBeforeUnmount, onMounted, ref, watch } from 'vue';

export default defineComponent({
    name: 'RuffleContent',
    props: {
        swfUrl: {
            type: String,
            required: true
        },
        width: {
            type: Number,
            required: true
        },
        height: {
            type: Number,
            required: true
        }
    },
    setup(props: { swfUrl: string; width: number; height: number }) {
        const containerRef = ref<HTMLDivElement | null>(null);
        const rufflePlayerElement = ref<any | null>(null);

        const mountPlayer = (): void => {
            const ruffleGlobal = (window as any).RufflePlayer;
            if (!ruffleGlobal || typeof ruffleGlobal.newest !== 'function') {
                return;
            }
            const ruffleInstance = ruffleGlobal.newest();
            const rufflePlayer = ruffleInstance.createPlayer();
            rufflePlayer.style.width = `${props.width}px`;
            rufflePlayer.style.height = `${props.height}px`;
            if (containerRef.value) {
                containerRef.value.appendChild(rufflePlayer);
            }
            rufflePlayerElement.value = rufflePlayer;
            rufflePlayer.ruffle().load({
                url: props.swfUrl,
                autoplay: 'on',
                unmuteOverlay: 'hidden'
            });
        };

        onMounted(() => {
            mountPlayer();
        });

        watch(
            () => props.swfUrl,
            (newUrl: string) => {
                if (rufflePlayerElement.value && typeof rufflePlayerElement.value.ruffle === 'function') {
                    rufflePlayerElement.value.ruffle().load(newUrl);
                }
            }
        );

        onBeforeUnmount(() => {
            if (rufflePlayerElement.value && typeof rufflePlayerElement.value.remove === 'function') {
                rufflePlayerElement.value.remove();
            }
            rufflePlayerElement.value = null;
        });

        return {
            containerRef
        };
    }
});
</script>
