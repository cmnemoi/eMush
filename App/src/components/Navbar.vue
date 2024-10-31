<template>
    <div class="nav-bar">
        <div class="right-section">
            <button class="username-button" @click="$emit(isPanelOpen ? 'close-panel' : 'toggle-panel')">
                {{ username }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps } from 'vue';

const props = defineProps({
    points: Number,
    username: String,
    isLoggedIn: Boolean,
    isPanelOpen: Boolean,
    togglePanel: Function
});
</script>

<style scoped lang="scss">
.hud {
  min-height: 100vh;
  background-color: transparent;
  color: white;
  font-family: Arial, sans-serif;
  position: relative;
}

.nav-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #000000;
  padding: 0.25rem 1rem;
  height: 32px;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1;
}

.left-section, .right-section {
  display: flex;
  align-items: center;
  margin-left: auto;
}

.character-list {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
  padding: 4px;
}

.character-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: #1e90ff;
  padding: 2px;
  transition: transform 0.2s ease;
}

.character-avatar:hover {
  transform: scale(1.1);
  cursor: pointer;
}

.avatar-inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background-color: #4169e1;
}

.character-add {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.character-add:hover {
  background-color: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.points {
  color: #ffd700;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 4px;
}

.username-container {
  display: flex;
  align-items: center;
  gap: 4px;
  background-color: #ff6b00;
  padding: 0.25rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.username-container:hover {
  background-color: #ff8533;
}

.username-container.active {
  background-color: #ff8533;
}

.username {
  font-weight: bold;
  text-transform: uppercase;
}

.dropdown-arrow {
  width: 12px;
  height: 12px;
  transition: transform 0.2s;
}

.dropdown-arrow.rotated {
  transform: rotate(180deg);
}

/* Overlay for clicking outside to close */
.panel-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 998;
}

.side-panel {
  position: fixed;
  right: 0;
  top: 0;
  height: 100%;
  width: 300px;
  background-color: #1a1a1a;
  padding: 1rem;
  transform: translateX(100%);
  transition: transform 0.3s ease;
  z-index: 999;
}

.panel-open {
  transform: translateX(0);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.close-button {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
  transition: opacity 0.2s;
}

.close-button:hover {
  opacity: 0.8;
}

.settings-section {
  background-color: #2a2a2a;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.settings-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.setting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.setting-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.icon-small {
  width: 16px;
  height: 16px;
  stroke: currentColor;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  fill: none;
}

.toggle {
  width: 40px;
  height: 24px;
  background-color: #4a4a4a;
  border-radius: 12px;
  cursor: pointer;
  position: relative;
  transition: background-color 0.2s;
}

.toggle::after {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  background-color: white;
  border-radius: 50%;
  top: 2px;
  left: 2px;
  transition: transform 0.2s;
}

.toggle-active {
  background-color: #3b82f6;
}

.toggle-active::after {
  transform: translateX(16px);
}

.language-selector {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.language-flag {
  width: 24px;
  height: 24px;
  background-color: #2a2a2a;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.625rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.language-flag:hover {
  background-color: #3b82f6;
}

.language-flag.active {
  background-color: #3b82f6;
}

.username-button {
  background-color: #ff6b00;
  border: none;
  color: white;
  font-weight: bold;
  text-transform: uppercase;
  padding: 0.3rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
  margin-left: auto;
}

.username-button:hover {
  background-color: #ff8533;
}
</style>
