<template>
    <div class="hud">
        <Navbar
            :points="points"
            :username="username"
            :is-logged-in="isLoggedIn"
            :is-panel-open="isPanelOpen"
            @toggle-panel="togglePanel"
        />

        <RightPanel
            :is-panel-open="isPanelOpen"
            :settings="settings"
            :languages="languages"
            :current-language="currentLanguage"
            :username="username"
            @toggle-setting="toggleSetting"
            @select-language="selectLanguage"
            @close-panel="closePanel"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import Navbar from './Navbar.vue';
import RightPanel from './RightPanel.vue';

const username = ref('EVIAN');
const points = ref(436);
const isLoggedIn = ref(true);
const isPanelOpen = ref(false);
const currentLanguage = ref('fr');

const icons = {
    clock: `<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>`,
    music: `<path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle>`
};

const settings = ref([
    { name: 'Faster fights', icon: icons.clock, enabled: true },
    { name: 'Background music', icon: icons.music, enabled: true }
]);

const languages = [
    { code: 'en', name: 'English' },
    { code: 'fr', name: 'French' },
    { code: 'de', name: 'German' },
    { code: 'ru', name: 'Russian' },
    { code: 'pt', name: 'Portuguese' }
];

const togglePanel = () => {
    isPanelOpen.value = !isPanelOpen.value;
};

const closePanel = () => {
    isPanelOpen.value = false;
};

const toggleSetting = (setting) => {
    setting.enabled = !setting.enabled;
};

const selectLanguage = (code) => {
    currentLanguage.value = code;
};
</script>

<style scoped>
  /* Add styles for Hud here */
</style>
