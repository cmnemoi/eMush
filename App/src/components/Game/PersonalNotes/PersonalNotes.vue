<template>
    <Teleport to=".grid-container.comms-panel-container" :disabled="!belowBreakpoint">
        <div
            class="personal-notes-window-wrapper"
            :style="belowBreakpoint ? {} : { top: position.y + 'px', left: position.x + 'px' }"
        >
            <div
                class="personal-notes-window"
                @click.stop
                @mousedown.stop
            >
                <div
                    class="window-header"
                    @mousedown="!belowBreakpoint && startDrag($event)"
                    @touchstart.prevent="!belowBreakpoint && startDrag($event)"
                >
                    <span>{{ $t('personalNotes.title') }}</span>
                    <button
                        class="close-btn"
                        @click.stop="$emit('close')"
                        @touchend.stop.prevent="$emit('close')"
                    >
                        <img :src="getImgUrl('comms/close.png')" alt="close">
                    </button>
                </div>
                <PersonalNotesTabs
                    :tabs="tabs"
                    :index="currentTab"
                    @update:tabs="setTabs"
                    @update:index="currentTab = $event"
                />
                <div class="window-content">
                    <div class="textarea-wrapper">
                        <textarea v-model="tabs[currentTab].content" @blur="updateTabs"/>
                    </div>
                    <div class="buttons-container">
                        <Tippy>
                            <div class="button" @click="displayIconPicker = !displayIconPicker; displayDeleteConfirm = false">
                                <img :src="getImgUrl('comms/buttonCharacters.png')" alt="☺">
                            </div>
                            <template #content>
                                <h1 v-html="formatContent($t('personalNotes.editTabIconTooltip'))"/>
                            </template>
                        </Tippy>
                        <EmotePicker
                            v-if="displayIconPicker"
                            title="personalNotes.editTabIconTitle"
                            @emote="editTabIcon"
                            @close="displayIconPicker = false;"
                            :offset-right="0"
                            popup
                        />
                        <Tippy v-if="tabs.length > 1">
                            <div class="button" @click="handleDeleteButton">
                                <img :src="getImgUrl('bin.png')" alt="🗑">
                            </div>
                            <template #content>
                                <h1 v-html="formatContent($t('personalNotes.deleteTabTooltip'))"/>
                            </template>
                        </Tippy>
                        <ConfirmPopOver
                            v-if="displayDeleteConfirm"
                            title="personalNotes.deleteTabTitle"
                            @yes="deleteTab"
                            @no="displayDeleteConfirm = false"
                            :offset-right="0"
                        />
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import PersonalNotesTabs from "@/components/Game/PersonalNotes/PersonalNotesTabs.vue";
import { PersonalNotes, PersonalNotesTab } from "@/entities/PersonalNotes";
import { Player } from "@/entities/Player";
import { useStore } from "vuex";
import PlayerService from "@/services/player.service";
import { Tippy } from "vue-tippy";
import EmotePicker from "@/components/Game/Emote/EmotePicker.vue";
import { emoteIconEnums } from "@/enums/emotes.enum";
import ConfirmPopOver from "@/components/Utils/ConfirmPopOver.vue";
import { useBreakpoint } from "@/utils/breakpoint";

defineEmits(['close']);
const props = defineProps<{ player: Player }>();
const store = useStore();

////////////////////////
// Handle tab edition //
////////////////////////
const tabs = ref(props.player!.personalNotes!.tabs);
const displayIconPicker = ref(false);
const displayDeleteConfirm = ref(false);
const currentTab = ref(0);
const updateTabs = () => {
    tabs.value.forEach((tab: PersonalNotesTab, i: number) => tab.index = i);
    PlayerService.updatePersonalNotes(props.player.id, tabs.value).then((notes: PersonalNotes) => {
        tabs.value.splice(0, tabs.value.length, ...notes.tabs);
        store.dispatch('player/updatePlayer', {
            ...store.state.player.player,
            tabs: notes.tabs
        });
    });
};
const setTabs = (newTabs: PersonalNotesTab[]) => {
    tabs.value.splice(0, tabs.value.length, ...newTabs);
    updateTabs();
};
const editTabIcon = (icon: string) => {
    displayIconPicker.value = false;
    tabs.value[currentTab.value].icon = emoteIconEnums[icon].img;
    updateTabs();
};
const deleteTab = () => {
    displayDeleteConfirm.value = false;
    if (tabs.value.length <= 1) return;
    tabs.value.splice(currentTab.value, 1);
    updateTabs();
    if (currentTab.value >= tabs.value.length) {
        currentTab.value = tabs.value.length - 1;
    }
};
const handleDeleteButton = () => {
    displayIconPicker.value = false;

    // Do not ask for confirmation if the tab is empty.
    if (!displayDeleteConfirm.value && tabs.value[currentTab.value].content.length === 0 ) {
        deleteTab();
        return;
    }

    displayDeleteConfirm.value = !displayDeleteConfirm.value;
};

////////////////////////////////////
// Handle phone fixed positioning //
////////////////////////////////////
const belowBreakpoint = useBreakpoint(660); // $breakpoint-desktop-s

////////////////////////////
// Handle window dragging //
////////////////////////////
const position = ref({ x: 100, y: 100 });
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });
const startDrag = (e: MouseEvent | TouchEvent) => {
    isDragging.value = true;
    const clientX = 'touches' in e ? e.touches[0].clientX : (e as MouseEvent).clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : (e as MouseEvent).clientY;
    dragOffset.value = {
        x: clientX - position.value.x,
        y: clientY - position.value.y
    };

    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', onDrag, { passive: false });
    document.addEventListener('touchend', stopDrag);
};
const stopDrag = () => {
    isDragging.value = false;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchmove', onDrag);
    document.removeEventListener('touchend', stopDrag);
};
const onDrag = (e: MouseEvent | TouchEvent) => {
    if (isDragging.value) {
        e.preventDefault();
        const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
        const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
        position.value = {
            x: clientX - dragOffset.value.x,
            y: clientY - dragOffset.value.y
        };
    }
};
</script>

<style scoped lang="scss">
.personal-notes-window-wrapper {
    overflow: visible;
    position: absolute;
    z-index: 500;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.9);
    pointer-events: auto;

    @media screen and (max-width: $breakpoint-desktop-s) {
        position: relative;
        order: -1;
        width: 100%;
        max-width: 100%;
        margin-bottom: 5px;
        box-shadow: none;
    }

    .personal-notes-window {
        overflow: hidden;
        min-height: 350px;
        min-width: 425px;
        background-color: rgba(35, 37, 100, 0.9);
        box-shadow: inset 0 0 12px 3px #3965fb, inset 0 0 0 2px #3965fb;
        text-align: center;
        font-size: 1.05em;
        font-weight: 700;
        text-transform: uppercase;
        line-height: 1.1em;
        color: #090a61;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        resize: both;

        @media screen and (max-width: $breakpoint-desktop-s) {
            width: 100%;
            min-width: 100%;
            max-height: 70vh;
            resize: none;
        }

        .window-header {
            padding: 5px 5px 0 5px;
            cursor: move;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            resize: vertical;
            user-select: none;
            touch-action: none;

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

        .window-content {
            display: flex;
            padding: 5px;
            flex: 1;

            .textarea-wrapper {
                background: #c2f3fc;
                padding: 3px;
                flex: 1;

                textarea {
                    flex: 1;
                    box-sizing: border-box;
                    resize: none;
                }
            }

            .buttons-container {
                position: absolute;
                display: flex;
                gap: 3px;
                right: 10px;
                bottom: 10px;
                flex-direction: row;
                align-items: flex-end;

                .button {
                    cursor: pointer;
                    @include button-style();

                    & {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                }
            }
        }
    }
}
</style>
