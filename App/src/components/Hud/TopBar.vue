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
            <LocaleChange />
        </div>

        <div class="rightSection">
            <UserSearchBar
                v-if="isLogged"
                class="topbar-search"
                :placeholder="$t('hud.topBar.searchUser')"
                @select="onUserSelected"
            />
            <a v-if="!isLogged" class="connectLink">
                <button class="badge register" @click="register">{{ $t('hud.topBar.register') }}</button>
                <button class="badge connect" @click="login">{{ $t('hud.topBar.login') }}</button>
            </a>
            <button v-if="isLogged" class="playerLogged" @click="openUserMenu">
                <Transition name="notification" mode="out-in">
                    <span
                        v-if="notificationsCount > 0"
                        :key="notificationsCount"
                        class="notifications"
                        :data-count="notificationsCount"
                    >
                        {{ $t('hud.topBar.notifications', { count: notificationsCount }) }}
                    </span>
                </Transition>
                <span class="usernameBadge">
                    {{ `< ${username}` }}
                </span>
            </button>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, Transition } from 'vue';
import { mapActions, mapGetters } from 'vuex';
import { getEternaltwinGames } from '@/utils/getEternaltwinGames';
import { Tippy } from 'vue-tippy';
import LocaleChange from '../Utils/LocaleChange.vue';
import { UserSearchBar } from '@/features/userSearch';
import { UserSearchResult } from '@/features/userSearch/models';

export default defineComponent({
    name: 'TopBar',
    components: { LocaleChange, Tippy, UserSearchBar },
    data() {
        return {
            time: ''
        };
    },
    computed: {
        ...mapGetters({
            isLogged: 'auth/loggedIn',
            username: 'auth/username',
            locale: 'locale/currentLocale',
            notificationsCount: 'notifications/notificationsCount'
        }),
        eternaltwinGames() {
            return getEternaltwinGames().sort(() => Math.random() - 0.5);
        }
    },
    methods: {
        ...mapActions({
            login: 'auth/redirectToLogin',
            openUserMenu: 'popup/openUserMenu',
            register: 'auth/redirectToRegister'
        }),
        onUserSelected(user: UserSearchResult) {
            this.$router.push({ name: 'UserPage', params: { userId: user.userId } });
        }
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
            gap: 4px;
            flex: 0 1 auto;
            max-width: 25%;
            padding-right: 6px;

            // Hide games from the 5th onwards on mobile
            .eternaltwinGame:nth-child(n+5) {
                display: none;
            }
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
            margin: 0 6px;
            min-width: 0;
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
        border-left: none;
        padding-left: 12px;
        min-width: 0;

        @media (max-width: 768px) {
            padding-left: 6px;
            flex: 0 1 auto;
            max-width: 45%;
        }

        .topbar-search {
            margin-right: 0;
            margin-left: -12px;
            padding-right: 12px;
            border-right: 1px dotted $mediumGrey;
            max-width: 180px;

            @media (max-width: 768px) {
                display: none;
            }
        }

        .connectLink {
            display: flex;
            flex-direction: row;

            .badge {
                cursor: pointer;
                font-size: 11pt;
                padding: 3px 12px;

                @media (max-width: 768px) {
                    font-size: 10pt;
                    padding: 2px 8px;
                }
            }

            .register {
                color: #feb500;
                font-weight: bold;
                text-shadow: 0 0 8px #fe7d00;

                @media (max-width: 768px) {
                    display: none;
                }
            }

            .connect {
                color: #b7b9c6;

                @media (max-width: 768px) {
                    font-size: 9pt;
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
                    max-width: 90px;
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

                // Smooth transitions for all visual properties
                transition: all 0.2s ease;
                transform: scale(1);

                &:hover {
                    transform: scale(1.05);
                    box-shadow: inset 0px 0px 10px $lightBlue, 0px 0px 6px $darkBlue, 0px 0px 20px $darkBlue;
                }

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

// Transition classes for notification
.notification-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.notification-leave-active {
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.notification-enter-from {
    opacity: 0;
    transform: scale(0.3) translateY(-10px);
}

.notification-leave-to {
    opacity: 0;
    transform: scale(0.8) translateX(10px);
}

.notification-enter-to {
    opacity: 1;
    transform: scale(1) translateY(0);
}

.notification-leave-from {
    opacity: 1;
    transform: scale(1) translateY(0);
}
</style>
