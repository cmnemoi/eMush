<template>
    <div class="banner">
        <div class="logo">
            <router-link v-if="loggedIn" :to="{ name: 'GamePage' }" @click="reloadData"><img :src="getImgUrl('logo_new.png')" alt="eMush logo"></router-link>
            <router-link v-else :to="{ name: 'HomePage' }"><img :src="getImgUrl('logo_new.png')" alt="eMush logo"></router-link>
        </div>
        <div class="mainmenu">
            <router-link v-if="loggedIn"  :to="{ name: 'GamePage' }">Daedalus</router-link>
            <router-link v-if="loggedIn" :to="{ name: 'MePage' }">{{ $t("banner.user") }}</router-link>
            <router-link v-if="loggedIn" :to="{ name: 'RankingPage' }">{{ $t("banner.ranking") }}</router-link>
            <router-link v-if="isAdmin" :to="{ name: 'Admin' }"> {{ $t('banner.admin') }}</router-link>
            <router-link v-if="isModerator && !isAdmin" :to="{ name: 'Moderation' }">{{ $t("banner.moderation") }}</router-link>
            <router-link :to="{ name: 'NewsPage' }">{{ $t("banner.news") }}</router-link>
            <a v-if="loggedIn" :href="forumLink">{{ $t("banner.forum") }}</a>
            <Login />
        </div>
    </div>
</template>

<script lang="ts">
import Login from "@/components/Login.vue";
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";
import { getImgUrl } from "@/utils/getImgUrl";


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
        ...mapGetters({
            loggedIn: 'auth/loggedIn',
            isAdmin: 'auth/isAdmin',
            isModerator: 'auth/isModerator',
            userId: 'auth/userId',
            player: 'player/player'
        }),
        route() {
            return this.$route.name;
        }
    },
    methods: {
        ...mapActions({
            loadAlivePlayerChannels: 'communication/loadAlivePlayerChannels',
            loadDeadPlayerChannels: 'communication/loadDeadPlayerChannels',
            loadRoomLogs: 'communication/loadRoomLogs',
            reloadPlayer: 'player/reloadPlayer'
        }),
        getImgUrl,
        async reloadData() {
            if (this.route !== 'GamePage' || this.player === null) return;
            await this.reloadPlayer();
            await Promise.all([
                this.loadRoomLogs(),
                this.player.isDead() ? this.loadDeadPlayerChannels() : this.loadAlivePlayerChannels()
            ]);
        }
    }
});
</script>

<style lang="scss" scoped>

.mainmenu {
    display: flex;
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
    row-gap: 0.5em;

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
