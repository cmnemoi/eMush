<template>
    <div class="banner">
        <div class="logo">
            <router-link v-if="loggedIn" :to="{ name: 'GamePage' }"><img src="@/assets/images/logo_new.png" alt=""></router-link>
            <router-link v-else :to="{ name: 'HomePage' }"><img src="@/assets/images/logo_new.png" alt=""></router-link>
        </div>
        <div class="mainmenu">
            <router-link v-if="loggedIn"  :to="{ name: 'GamePage' }">Daedalus</router-link>
            <router-link v-if="loggedIn" :to="{ name: 'MePage' }">{{ $t("banner.user") }}</router-link>
            <router-link v-if="loggedIn" :to="{ name: 'RankingPage' }">{{ $t("banner.ranking") }}</router-link>
            <router-link v-if="isAdmin" :to="{ name: 'Admin' }">Admin</router-link>
            <router-link v-if="loggedIn" :to="{ name: 'NewsPage' }">{{ $t("banner.news") }}</router-link>
            <a v-if="loggedIn" :href="forumLink">{{ $t("banner.forum") }}</a>
            <router-link v-if="loggedIn" :to="{ name: 'ImportPage' }">{{ $t("banner.import") }}</router-link>
            <Login />
        </div>
    </div>
</template>

<script lang="ts">
import Login from "@/components/Login.vue";
import { defineComponent } from "vue";
import { mapGetters } from "vuex";


export default defineComponent ({
    name: 'Banner',
    components: {
        Login
    },
    data() {
        return {
            forumLink: "https://eternaltwin.org/forum/sections/b5ddc792-0738-4289-9818-c2f1f029c8b1"
        };
    },
    computed: {
        ...mapGetters('auth', [
            'loggedIn',
            'isAdmin',
            'userId'
        ])
    }
});
</script>

<style lang="scss" scoped>

.mainmenu {
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
