<template>
    <div class="user-search-bar">
        <div class="search-input-container">
            <input
                ref="searchInputRef"
                v-model="searchQuery"
                type="text"
                :placeholder="placeholder"
                class="search-input"
                @input="onInput"
                @focus="showDropdown = true"
                @blur="onBlur"
                @keydown.enter="onEnter"
            />
            <span class="search-icon">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </span>
            <ul v-if="showDropdown && results.length > 0" class="search-results">
                <li
                    v-for="result in results"
                    :key="result.userId"
                    class="search-result-item"
                    @mousedown.prevent="selectUser(result)"
                >
                    {{ result.username }}
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { useStore } from "vuex";
import { UserSearchResult } from "./models";

const props = withDefaults(defineProps<{
    placeholder?: string;
    debounceMs?: number;
}>(), {
    placeholder: "Search for a user",
    debounceMs: 300
});

const emit = defineEmits<{
    select: [user: UserSearchResult];
}>();

const store = useStore();
const searchQuery = ref("");
const showDropdown = ref(false);
const searchInputRef = ref<HTMLInputElement | null>(null);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const results = computed((): UserSearchResult[] => store.getters["userSearch/results"]);

const onInput = () => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        if (searchQuery.value.trim()) {
            store.dispatch("userSearch/search", { username: searchQuery.value, limit: 3 });
        } else {
            store.dispatch("userSearch/clear");
        }
    }, props.debounceMs);
};

const onBlur = () => {
    setTimeout(() => {
        showDropdown.value = false;
    }, 200);
};

const onEnter = () => {
    if (results.value.length > 0) {
        selectUser(results.value[0]);
    }
};

const selectUser = (user: UserSearchResult) => {
    searchQuery.value = "";
    showDropdown.value = false;
    store.dispatch("userSearch/clear");
    searchInputRef.value?.blur();
    emit("select", user);
};
</script>

<style lang="scss" scoped>
.user-search-bar {
    position: relative;
    width: 100%;
    max-width: 300px;
}

.search-input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input {
    width: 100%;
    padding: 0.25em 2em 0.25em 0.6em;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid $mediumGrey;
    border-radius: 3px;
    color: white;
    font-size: 0.85em;
    outline: none;

    &::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    &:hover {
        border-color: $orange;
    }

    &:focus {
        border-color: $lightOrange;
        box-shadow: 0 0 0 2px rgba($orange, 0.3);
    }
}

.search-icon {
    position: absolute;
    right: 0.5em;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    pointer-events: none;
    color: $mediumGrey;
    transition: color 0.15s;

    svg {
        width: 14px;
        height: 14px;
    }
}

.search-input:hover + .search-icon,
.search-input:focus + .search-icon {
    color: $orange;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    background: rgba(0, 0, 0, 0.9);
    border: 1px solid $orange;
    border-top: none;
    border-radius: 0 0 3px 3px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 100;
}

.search-result-item {
    display: block;
    width: 100%;
    padding: 0.6em 0.8em;
    cursor: pointer;
    color: white;
    transition: background-color 0.15s;

    &:hover {
        background: rgba($orange, 0.3);
    }

    &:not(:last-child) {
        border-bottom: 1px solid rgba($orange, 0.3);
    }
}
</style>
