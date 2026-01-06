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

<style lang="scss" scoped>

.sub-menu {
    display: flex;
    flex-direction: row;
    margin: 1.8em auto -1px auto;
    padding: 0 20px;
    max-width: $breakpoint-desktop-l;
    width: 100%;

    a {
        background-color: rgba(15, 89, 171, 0.55);
        min-width: 130px;
        padding: 12px 16px;
        margin-right: 10px;
        color: white;
        text-align: center;
        font-weight: normal;
        letter-spacing: .06em;
        text-transform: uppercase;
        text-decoration: none;

        @include corner-bezel(14px, 14px, 0);

        opacity: .75;

        &:hover,
        &:active,
        &.router-link-active { opacity: 1; }
    }
}

</style>
