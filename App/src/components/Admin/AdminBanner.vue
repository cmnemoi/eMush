<template>
    <div class="banner">
        <div class="admin-menu">
            <router-link :to="{ name: 'AdminDaedalus' }">Daedalus</router-link>
            <router-link :to="{ name: 'AdminGameConfig' }">Game Config</router-link>
            <router-link :to="{ name: 'AdminUser' }">Users</router-link>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";


export default defineComponent ({
    name: 'AdminBanner',
});
</script>

<style lang="scss" scoped>

.admin-menu {
    display: flex;
    flex-direction: row;

    span {
        margin: 0 1.4em;
        padding: .3em .6em;
        color: white;
        font-size: 1.1rem;
        font-weight: normal;
        letter-spacing: .06em;
        text-decoration: none;
    }

    a {
        margin: 0 1.4em;
        padding: .3em .6em;
        color: white;
        font-size: 1.1rem;
        font-weight: normal;
        letter-spacing: .06em;
        text-decoration: none;

        &:hover,
        &:active {
            color: #dffaff;
            text-shadow: 0 0 1px white, 0 0 1px white;
        }

        .unavailable {
            text-decoration: line-through;
            opacity: 0.6;
        }
    }
}

.banner {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.logo {
    height: 100%;
    margin: -0.05em 5em 0.1em 5em;
}

</style>
