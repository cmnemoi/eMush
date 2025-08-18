<template>
    <div id="topBar">
        <div class="leftSection">
            <Tippy
                tag="a"
                v-for="game in eternaltwinGames"
                :key="game.key"
                :href="game.link"
                target="_blank"
                rel="noopener noreferrer"
                class="eternaltwinGame"
            >
                <img :src="game.icon" :alt="$t(`footer.eternaltwinGames.${game.key}.name`)" class="logo" />
                <template #content>
                    <h1>{{ $t(`footer.eternaltwinGames.${game.key}.name`) }}</h1>
                    <p>{{ $t(`footer.eternaltwinGames.${game.key}.description`) }}</p>
                </template>
            </Tippy>
        </div>

        <div class="centerSection">
            <span class="time">{{ time }}</span>
        </div>

        <div class="rightSection">
            <a v-if="!isLogged" class="connectLink" @click="login">
                <button class="connectBadge">{{ $t('hud.topBar.login') }}</button>
            </a>
            <button v-if="isLogged" class="playerLogged" @click="openUserMenu">
                <span class="notifications" v-if="notificationsCount > 0" :data-count="notificationsCount">{{ $t('hud.topBar.notifications', { count: notificationsCount }) }}</span>
                <span class="usernameBadge">
                    {{ `< ${username}` }}
                </span>
            </button>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mapActions, mapGetters } from 'vuex';
import { getEternaltwinGames } from '@/utils/getEternaltwinGames';
import { Tippy } from 'vue-tippy';

export default defineComponent({
    name: 'TopBar',
    components: { Tippy },
    data() {
        return {
            notificationsCount: 0,
            time: ''
        };
    },
    computed: {
        ...mapGetters({
            isLogged: 'auth/loggedIn',
            username: 'auth/username'
        }),
        eternaltwinGames() {
            return getEternaltwinGames().sort(() => Math.random() - 0.5);
        }
    },
    methods: {
        ...mapActions({
            login: 'auth/redirectToLogin',
            openUserMenu: 'popup/openUserMenu'
        }),
        getTime() {
            return new Date().toLocaleTimeString(this.$i18n.locale, { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    },
    mounted() {
        setInterval(() => this.time = this.getTime(), 1_000);
    }
});
</script>

<style scoped lang="scss">

#topBar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    height: 32px;
    padding: 0 12px;
    background-color: $darkGrey;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);

    @media (max-width: 768px) {
        padding: 0 8px;
        height: 32px;
    }

    .leftSection {
        flex: 0 0 auto;
        display: flex;
        flex-direction: row;
        gap: 12px;
        min-width: 0;
        overflow: hidden;
        padding-right: 12px;
        border-right: 1px dotted $mediumGrey;

        @media (max-width: 768px) {
            gap: 6px;
            flex: 0 1 auto;
            max-width: 30%;
        }

        .eternaltwinGame {
            display: flex;
            padding: 1px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
            flex-shrink: 0;

            &:hover {
                background-color: $orange;
            }

            @media (max-width: 768px) {
                padding: 0;
            }
        }

        .logo {
            height: 16px;
            width: auto;

            @media (max-width: 768px) {
                height: 14px;
            }
        }
    }

    .centerSection {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1;

        @media (max-width: 768px) {
            position: static;
            transform: none;
            flex: 1;
            margin: 0 8px;
        }

        .time {
            color: $blue;
            font-size: 0.9rem;
            line-height: 1;
            white-space: nowrap;

            @media (max-width: 768px) {
                font-size: 0.8rem;
                text-align: center;
            }
        }
    }

    .rightSection {
        flex: 0 0 auto;
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
        align-items: center;
        border-left: 1px dotted $mediumGrey;
        padding-left: 12px;
        min-width: 0;

        @media (max-width: 768px) {
            padding-left: 8px;
            flex: 0 1 auto;
            max-width: 40%;
        }

        .connectLink {
            text-decoration: none;

            .connectBadge {
                background-color: $orange;
                color: white;
                border: none;
                padding: 0 8px;
                border-radius: 3px;
                font-size: 0.8rem;
                font-weight: 600;
                cursor: pointer;
                transition: background-color 0.2s ease;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                white-space: nowrap;

                @media (max-width: 768px) {
                    padding: 0 6px;
                    font-size: 0.7rem;
                    height: 22px;
                }

                &:hover {
                    background-color: $lightOrange;
                }
            }
        }

        .playerLogged {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 6px;
            background-color: transparent;
            border: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 3px;
            transition: background-color 0.2s ease;
            min-width: 0;

            @media (max-width: 768px) {
                padding: 2px 4px;
                gap: 4px;
            }

            &:hover {
                background-color: $lightOrange;

                .usernameBadge {
                    color: white;
                }
            }

            .usernameBadge {
                color: $lightGrey;
                font-size: 12pt;
                transition: color 0.2s ease;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;

                @media (max-width: 768px) {
                    font-size: 10pt;
                    max-width: 80px;
                }
            }

            .notifications {
                display: inline-block;
                margin-right: 10px;
                height: auto;
                padding: 2px 6px;
                color: white;
                font-size: 9pt;
                font-weight: bold;
                line-height: 1.2em;
                text-align: center;
                box-shadow: inset 0px 0px 8px $lightBlue, 0px 0px 4px $darkBlue, 0px 0px 16px $darkBlue;
                background: $brightBlue;
                border-radius: 3px;
                flex-shrink: 0;

                @media (max-width: 768px) {
                    font-size: 0;
                    min-width: 16px;
                    min-height: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 6px;
                    padding: 0;

                    &::before {
                        content: attr(data-count);
                        font-size: 9pt;
                        line-height: 1.2em;
                    }
                }
            }
        }
    }
}
</style>
