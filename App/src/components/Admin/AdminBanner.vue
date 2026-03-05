<template>
    <div class="sub-menu">
        <router-link :to="{ name: 'AdminDaedalusList' }">Daedalus</router-link>
        <router-link :to="{ name: 'AdminConfigHomepage' }">Configs</router-link>
        <router-link :to="{ name: 'AdminPlayerList' }">Players</router-link>
        <router-link :to="{ name: 'AdminUser' }">Users</router-link>
        <router-link :to="{ name: 'AdminNewsList' }">News</router-link>
        <router-link :to="{ name: 'AdminActionsPage' }">{{ $t('admin.banner.actions') }}</router-link>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";


export default defineComponent ({
    name: 'AdminBanner'
});
</script>
