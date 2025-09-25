<template>
    <UserBanner />
    <div class="User-container">
        <div class="box-container">
            <h2>{{ $t('userProfile.pageTitle') }} {{ user.username }}</h2>
            <router-view />
        </div>
    </div>
</template>

<script setup lang="ts">
import UserBanner from "@/components/User/UserBanner.vue";
import { User } from "@/entities/User";
import { watch } from "vue";
import { computed, onBeforeMount } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "vuex";

const route = useRoute();
const store = useStore();

const user = computed((): User => store.getters['userProfile/user']);

onBeforeMount(() => store.dispatch('userProfile/loadUser', route.params.userId as string));
watch(route, () => store.dispatch('userProfile/loadUser', route.params.userId as string));
</script>

<style lang="scss" scoped>

.box-container {
    margin-top: 0;
}

</style>
