<template>
    <div class="tabs">
        <div
            v-for="(tab, i) in tabs"
            :key="i"
            class="tab"
            :class="{ active: i === index, dragging: draggedTabIndex === i }"
            :style="touchDraggedIndex === i ? { transform: `translateX(${touchDragOffset}px)` } : {}"
            draggable="true"
            @click="onTabClick(i)"
            @dragstart="onTabDragStart($event, i)"
            @dragover.prevent="onTabDragOver($event, i)"
            @drop="emit('update:tabs', tabs);"
            @dragend="onTabDragEnd"
            @touchstart="onTabTouchStart($event, i)"
            @touchmove.prevent="onTabTouchMove($event, i)"
            @touchend="onTabTouchEnd"
        >
            <img :src="tab.icon ?? getImgUrl('ui_icons/project.png')" alt="tab">
        </div>
        <div v-if="tabs.length < 16" class="tab" @click="displayIconPicker = true">
            <img :src="getImgUrl('comms/newtab.png')" alt="tab">
        </div>
        <EmotePicker
            v-if="displayIconPicker"
            title="personalNotes.iconPicker"
            @emote="createNewTab"
            @close="displayIconPicker = false;"
            popup
        />
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import EmotePicker from "@/components/Game/Emote/EmotePicker.vue";
import { PersonalNotesTab } from "@/entities/PersonalNotes";
import { emoteIconEnums } from "@/enums/emotes.enum";

const props = defineProps<{ tabs: PersonalNotesTab[], index: number}>();
const emit = defineEmits<{
    'update:tabs': [tabs: PersonalNotesTab[]]
    'update:index': [index: number]
}>();


/////////////////////////////
// Handle new tab creation //
/////////////////////////////
const displayIconPicker = ref(false);
const createNewTab = (icon: string) => {
    displayIconPicker.value = false;
    if (props.tabs.length >= 16) return;
    const newTabs = [...props.tabs];
    newTabs.push(new PersonalNotesTab(null, newTabs.length, emoteIconEnums[icon].img, ''));
    emit('update:tabs', newTabs);
    emit('update:index', newTabs.length - 1);
};

/////////////////////////////////
// Handle tab dragging (mouse) //
/////////////////////////////////
const draggedTabIndex = ref<number | null>(null);
const onTabDragStart = (e: DragEvent, index: number) => {
    draggedTabIndex.value = index;
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
    }
};
const onTabDragOver = (e: DragEvent, index: number) => {
    if (draggedTabIndex.value !== null && draggedTabIndex.value !== index) {
        const draggedTab = props.tabs[draggedTabIndex.value];
        const newTabs = [...props.tabs];
        newTabs.splice(draggedTabIndex.value!, 1);
        newTabs.splice(index, 0, draggedTab);
        emit('update:tabs', newTabs);

        if (props.index === draggedTabIndex.value) {
            emit('update:index', index);
        } else if (draggedTabIndex.value < props.index && index >= props.index) {
            emit('update:index', props.index - 1);
        } else if (draggedTabIndex.value > props.index && index <= props.index) {
            emit('update:index', props.index + 1);
        }

        draggedTabIndex.value = index;
    }
};
const onTabDragEnd = () => {
    draggedTabIndex.value = null;
};

/////////////////////////////////
// Handle tab dragging (touch) //
/////////////////////////////////
const LONG_PRESS_DELAY = 300; // ms
const touchDraggedIndex = ref<number | null>(null);
const touchStartX = ref(0);
const touchStartTime = ref(0);
const touchDragOffset = ref(0);
const touchLongPressTimer = ref<number | null>(null);
const isTouchDragging = ref(false);
const onTabClick = (index: number) => {
    // Only switch tabs if we're not dragging
    if (!isTouchDragging.value) {
        emit('update:index', index);
    }
};
const onTabTouchStart = (e: TouchEvent, index: number) => {
    touchStartX.value = e.touches[0].clientX;
    touchStartTime.value = Date.now();
    touchDraggedIndex.value = index;
    isTouchDragging.value = false;
    touchLongPressTimer.value = window.setTimeout(() => {
        isTouchDragging.value = true;
        draggedTabIndex.value = index;
    }, LONG_PRESS_DELAY);
};
const onTabTouchMove = (e: TouchEvent, index: number) => {
    if (!isTouchDragging.value) {
        const deltaX = Math.abs(e.touches[0].clientX - touchStartX.value);
        if (deltaX > 10 && touchLongPressTimer.value) {
            clearTimeout(touchLongPressTimer.value);
            touchLongPressTimer.value = null;
            touchDraggedIndex.value = null;
        }
        return;
    }

    const touch = e.touches[0];
    const deltaX = touch.clientX - touchStartX.value;
    touchDragOffset.value = deltaX;

    const tabWidth = 31 + 2; // max-width + gap
    const moveThreshold = tabWidth / 2;
    const indexOffset = Math.round(deltaX / tabWidth);
    const targetIndex = Math.max(0, Math.min(props.tabs.length - 1, touchDraggedIndex.value! + indexOffset));

    if (targetIndex !== touchDraggedIndex.value && Math.abs(deltaX) > moveThreshold) {
        const draggedTab = props.tabs[touchDraggedIndex.value!];
        const newTabs = [...props.tabs];
        newTabs.splice(touchDraggedIndex.value!, 1);
        newTabs.splice(targetIndex, 0, draggedTab);
        emit('update:tabs', newTabs);

        if (props.index === touchDraggedIndex.value) {
            emit('update:index', index);
        } else if (touchDraggedIndex.value! < props.index && targetIndex >= props.index) {
            emit('update:index', props.index - 1);
        } else if (touchDraggedIndex.value! > props.index && targetIndex <= props.index) {
            emit('update:index', props.index + 1);
        }

        touchDraggedIndex.value = targetIndex;
        touchStartX.value = touch.clientX;
        touchDragOffset.value = 0;
    }
};
const onTabTouchEnd = () => {
    if (touchLongPressTimer.value) {
        clearTimeout(touchLongPressTimer.value);
        touchLongPressTimer.value = null;
    }

    if (isTouchDragging.value) {
        emit('update:tabs', props.tabs);
    }

    touchDraggedIndex.value = null;
    draggedTabIndex.value = null;
    touchDragOffset.value = 0;
    isTouchDragging.value = false;
};
</script>

<style scoped lang="scss">
.tabs {
    display: flex;
    flex-wrap: nowrap;
    flex-direction: row;
    justify-content: start;
    gap: 2px;
    padding: 5px 5px 0 5px;
    margin-top: 5px;
    margin-bottom: -5px;

    .tab {
        position: relative;
        display: flex;
        flex: 1;
        align-items: center;
        justify-content: center;
        overflow: visible;
        float: left;
        cursor: pointer;
        max-width: 31px;
        height: 25px;

        * {
            z-index: 2;
        }

        &::after { // Background of the tab icons
            content: "";
            z-index: 1;
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(0deg, rgb(0, 116, 223) 6%, rgb(0, 116, 223) 46%, rgb(0, 142.0322394619, 229.018) 54%, rgb(0, 142.0322394619, 229.018) 94%, rgb(72.999125, 154.1474971973, 228.999875) 96%);

            @include corner-bezel(4.5px, 4.5px, 0);
        }

        &.active,
        &:hover,
        &:focus {
            &::after {
                background: rgba(194, 243, 252, 1);
            }
        }

        &.dragging {
            opacity: 0.5;
            transition: none; // Disable transitions during drag
        }

        &:active {
            cursor: grabbing;
        }

        // Smooth transform for touch drag
        transition: transform 0.1s ease-out;

        img {
            max-width: 16px;
            max-height: 16px;
        }
    }
}
</style>
