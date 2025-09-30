<template>
    <div class="User-container">
        <div class="box-container">
            <h2>{{ $t('userProfile.pageTitle', { user: user.username }) }}</h2>
            <div class="user-content-layout">
                <div class="user-ships-section">
                    <UserShipHistory  />
                </div>
                <div class="user-sidebar">
                    <UserAchievements v-if="user.id" :user="user" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { User } from "@/features/userProfile/models";
import UserShipHistory from "@/features/userProfile/UserShipHistory.vue";
import { watch } from "vue";
import { computed, onBeforeMount } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "vuex";
import UserAchievements from "@/features/achievements/UserAchievements.vue";

const route = useRoute();
const store = useStore();

const user = computed((): User => store.getters['userProfile/user']);

onBeforeMount(async () => await store.dispatch('userProfile/loadUser', route.params.userId as string));
watch(route, async () => await store.dispatch('userProfile/loadUser', route.params.userId as string));
</script>

<style lang="scss" scoped>
.user-content-layout {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: 0;
    align-items: stretch;
}

.user-card {
    position: relative;
    padding: 12px 14px;
    border: 1px solid #387fff;
    background: rgba(34, 38, 102, 0.6);
    box-shadow: inset 0 0 16px rgba(57, 101, 251, 0.32);
    @include corner-bezel(10px, 0);
    display: flex;
    flex-direction: column;
}

.user-section-title {
    margin: 0 0 6px 0;
    color: #cfe7ff;
    font-size: 1em;
    letter-spacing: .01em;
}

.user-section-body {
    margin: 0;
    color: #b8c6ff;
    font-style: italic;
    font-size: .95em;
}
</style>
