<template>
    <div class="banner">
        <div class="logo">
            <router-link v-if="loggedIn" :to="{ name: 'GamePage' }" @click="reloadData"><img :src="getImgUrl('logo_beta.png')" alt="eMush logo"></router-link>
            <router-link v-else :to="{ name: 'HomePage' }"><img :src="getImgUrl('logo_beta.png')" alt="eMush logo"></router-link>
        </div>
        <div class="mainmenu" :class="{ 'few-links': visibleLinksCount <= 4 }">
            <router-link v-if="loggedIn && visibleLinksCount <= 4" :to="{ name: 'GamePage' }" @click="reloadData">Daedalus</router-link>
            <router-link v-if="loggedIn" :to="{ name: 'MePage' }">{{ $t("banner.user") }}</router-link>
            <router-link v-if="loggedIn" class="hide-on-tiny-devices-800" :to="{ name: 'RankingPage' }">{{ $t("banner.ranking") }}</router-link>
            <router-link v-if="isAdmin" class="hide-on-tiny-devices-800" :to="{ name: 'Admin' }">{{ $t('banner.admin') }}</router-link>
            <router-link v-if="isModerator && !isAdmin" class="hide-on-tiny-devices-800" :to="{ name: 'Moderation' }">{{ $t("banner.moderation") }}</router-link>
            <router-link :to="{ name: 'NewsPage' }">{{ $t("banner.news") }}</router-link>
            <router-link v-if="loggedIn" class="hide-on-tiny-devices-800" :to="{ name: 'Rules' }">{{ $t("banner.rules") }}</router-link>
            <a
                :href="discordLink"
                class="hide-on-tiny-devices-400"
                target="_blank"
                rel="noreferrer noopener">{{ $t("banner.discord") }}</a>
            <a
                v-if="loggedIn"
                :href="wikiLink"
                target="_blank"
                rel="noreferrer noopener">{{ $t("banner.wiki") }}</a>
            <a v-if="loggedIn && replaceWithIcon" class="logout-button" @click="logoutAndRedirectToHome"> <img :src="getImgUrl('logout.png')" alt="Button Logout"></a>
            <a v-else-if="loggedIn" class="logout-buton">{{ $t('logout') }}</a>
            <a v-else class="login-button" @click="redirectToLogin">{{ $t('login') }}</a>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Player } from "@/entities/Player";
import { GameLocales } from "@/i18n";
import { getImgUrl } from "@/utils/getImgUrl";
import { getDiscordLink, getWikiLink } from "@/utils/links";
import { computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";

const store = useStore();
const route = useRoute();
const router = useRouter();

const discordLink = getDiscordLink();

const loggedIn = computed((): boolean => store.getters['auth/loggedIn']);
const isAdmin = computed((): boolean => store.getters['auth/isAdmin']);
const isModerator = computed((): boolean => store.getters['auth/isModerator']);
const player = computed((): Player | null => store.getters['player/player']);
const locale = computed((): GameLocales => store.getters['locale/currentLocale']);
const wikiLink = computed(() => getWikiLink(locale.value));

const visibleLinksCount = computed((): number => {
    let count = 3; // News + Discord + Login

    if (loggedIn.value) {
        count += 2; // Daedalus + My account
    }

    return count;
});

const replaceWithIcon = computed((): boolean => {
    return innerWidth <= 480;
});

const routeName = computed(() => route.name);

const logoutAndRedirectToHome = () => {
    store.dispatch('auth/logout');
    router.push('/');
};
const redirectToLogin = () => store.dispatch('auth/redirectToLogin');
const reloadData = async () => {
    if (routeName.value !== 'GamePage' || player.value === null) {
        return;
    }

    await store.dispatch('player/reloadPlayer');
    await Promise.all([
        store.dispatch('communication/loadRoomLogs'),
        player.value.isDead() ? store.dispatch('communication/loadDeadPlayerChannels') : store.dispatch('communication/loadAlivePlayerChannels')
    ]);
};
</script>

<style lang="scss" scoped>

.mainmenu {
    $color: rgba(32, 50, 129, 0.411);

    display: flex;
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.5em 0;

    ul {
        flex-direction: column;
        justify-content: center;
        row-gap:55px;
        align-items: center;
    }

    a {
        padding: 1em ;
        color: white;
        font-size: 1.1rem;
        font-weight: normal;
        letter-spacing: .06em;
        text-decoration: none;
        text-align: center;

        background-color: $color;

        &:first-child {
            border-top-left-radius: 50px 50px;
            border-bottom-left-radius: 50px 50px;
        }
        &:last-child {
            border-top-right-radius: 50px 50px;
            border-bottom-right-radius: 50px 50px;
        }

        &:hover,
        &:active {
            color: #dffaff;
            text-shadow: 0 0 1px white, 0 0 1px white;
        }

        .unavailable {
            text-decoration: line-through;
            opacity: 0.6;
        }

        // Handle mobile devices
        @media (max-width: 768px) {

            // Reset all corners
            border-radius: 0;

            // Round only the first and last buttons
            &:first-child {
                border-top-left-radius: 25px;
                border-bottom-left-radius: 25px;
            }
            &:last-child {
                border-top-right-radius: 25px;
                border-bottom-right-radius: 25px;
            }
        }
    }

    // Links are bigger when they are few of them
    &.few-links {
        a {
            @media (max-width: 768px) {
                padding: 1em;
                font-size: 1.1rem;
            }
        }
    }

    .hide-on-tiny-devices-800 {
        @media (max-width: 800px) {
            display: none;
        }
    }
    .hide-on-tiny-devices-400 {
        @media (max-width: 400px) {
            display: none;
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
    img {
         width: 300px;
    }
}

.login-button,
.logout-button {
    cursor: pointer;
    padding: 1em;
    color: white;
    font-size: 1.1em;
    letter-spacing: .06em;
    text-align: center;

    max-width: 200px;

    &:hover,
    &:active {
        color: #dffaff;
        text-shadow: 0 0 1px rgb(255, 255, 255), 0 0 1px rgb(255, 255, 255);
    }
}

</style>
