
<template>
    <div class="emote-picker-wrapper" :class="{ 'popup': popup }">
        <div class="emote-picker" :class="{ 'popup': popup }">
            <ul class="emote-tabs">
                <EmoteTab
                    v-for="(button, i) in EmoteTabs"
                    :key="i"
                    :config="button"
                    :selected="selectedEmoteTab == i"
                    @select="selectedEmoteTab = i"
                />
            </ul>
            <EmoteGrid :config="EmoteTabs[selectedEmoteTab]"  @emote="$emit('emote', $event)"/>
        </div>
    </div>
</template>


<script setup lang="ts">
import EmoteTab from "@/components/Game/Emote/EmoteTab.vue";
import EmoteGrid from "@/components/Game/Emote/EmoteGrid.vue";
import { EmoteTabs } from "@/components/Game/Emote/EmoteConfig";
import { ref } from "vue";

const selectedEmoteTab = ref(0);

defineProps({
    popup: {
        type: Boolean,
        default: false
    }
});

defineEmits(['emote']);
</script>

<style scoped lang="scss">
.emote-picker-wrapper {

    &.popup {
        overflow: auto;
        position: absolute;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.9);
        pointer-events: auto;
    }

    .emote-picker {
        display: flex;
        flex-direction: column;
        gap: 5px;
        max-width: 420px;

        &.popup {
            z-index: 1000;
            padding: 10px;
            background-color: rgba(35, 37, 100, 0.9);
            box-shadow: inset 0 0 12px 3px #3965fb, inset 0 0 0 2px #3965fb;
            pointer-events: auto;
        }

        .emote-tabs {
            justify-content: space-between;
            margin-right: -4px;
        }
    }
}
</style>
