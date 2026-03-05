<template>
    <div class="sub-menu">
        <router-link :to="{ name: 'StatsPlayersPage' }">Players</router-link>
        <router-link :to="{ name: 'StatsExplorationsPage' }">Explorations</router-link>
        <router-link :to="{ name: 'StatsMushPage' }">Mush</router-link>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";


export default defineComponent ({
    name: 'StatsBanner'
});
</script>
