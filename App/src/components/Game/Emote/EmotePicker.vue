
<template>
    <div
        class="emote-picker-wrapper"
        :class="{ 'popup': popup }"
        :style="popup ? {
            top: offsetTop !== null ? `${offsetTop}px` : undefined,
            bottom: offsetBottom !== null ? `${offsetBottom}px` : undefined,
            left: offsetLeft !== null ? `${offsetLeft}px` : undefined,
            right: offsetRight !== null ? `${offsetRight}px` : undefined
        } : {}"
    >
        <div class="emote-picker" :class="{ 'popup': popup }">
            <div class="emote-header">
                <span v-if="title">{{ $t(title) }}</span>
                <button class="close-btn" @click="$emit('close')"><img :src="getImgUrl('comms/close.png')" alt="close">
                </button>
            </div>
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
import { getImgUrl } from "@/utils/getImgUrl";

const selectedEmoteTab = ref(0);

defineProps({
    popup: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        required: false,
        default: null
    },
    offsetTop: {
        type: Number,
        default: null
    },
    offsetBottom: {
        type: Number,
        default: null
    },
    offsetLeft: {
        type: Number,
        default: null
    },
    offsetRight: {
        type: Number,
        default: null
    }
});

defineEmits(['emote', 'close']);
</script>

<style scoped lang="scss">
.emote-picker-wrapper {

    &.popup {
        overflow: auto;
        position: absolute;
        z-index: 2000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.9);
        pointer-events: auto;
        max-width: 420px;

        @media screen and (max-width: $breakpoint-mobile-l) {
            max-width: 300px;
        }
    }

    .emote-header {
        align-items: center;
        color: white;

        button {
            cursor: pointer;
            position: absolute;
            right: 0;
            top: 0;
            margin: 0.3em;
            padding: 0.2em;
            border-radius: 3px;
            transition: all 0.15s;
        }
    }

    .emote-picker {
        display: flex;
        flex-direction: column;
        gap: 5px;
        max-width: 420px;

        &.popup {
            z-index: 2000;
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
