<template>
    <Transition name="opacity">
        <div v-show="userMenu.isOpen" class="backDrop" @click="closeUserMenu"></div>
    </Transition>
    <Transition name="slide">
        <div v-show="userMenu.isOpen" class="root">
            <div class="player">
                <div class="close">
                    <button @click="closeUserMenu" class="burgerClose">
                        <svg
                            class="svgIcon"
                            focusable="false"
                            aria-hidden="true"
                            viewBox="0 0 24 24"
                            data-testid="MenuIcon">
                            <path d="M3 18h18v-2H3zm0-5h18v-2H3zm0-7v2h18V6z"></path>
                        </svg>
                    </button>
                </div>
                <h2 class="name">{{ username }}</h2>
            </div>
            <div class="shortCutMenu">
                <div class="shortCuts">
                    <router-link to="/game" class="link" @click="closeUserMenu">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="svgLinkIcon size-6">
                            <path fill-rule="evenodd" d="M9.315 7.584C12.195 3.883 16.695 1.5 21.75 1.5a.75.75 0 0 1 .75.75c0 5.056-2.383 9.555-6.084 12.436A6.75 6.75 0 0 1 9.75 22.5a.75.75 0 0 1-.75-.75v-4.131A15.838 15.838 0 0 1 6.382 15H2.25a.75.75 0 0 1-.75-.75 6.75 6.75 0 0 1 7.815-6.666ZM15 6.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" clip-rule="evenodd" />
                            <path d="M5.26 17.242a.75.75 0 1 0-.897-1.203 5.243 5.243 0 0 0-2.05 5.022.75.75 0 0 0 .625.627 5.243 5.243 0 0 0 5.022-2.051.75.75 0 1 0-1.202-.897 3.744 3.744 0 0 1-3.008 1.51c0-1.23.592-2.323 1.51-3.008Z" />
                        </svg>


                        <span>{{ $t('hud.userMenu.daedalus') }}</span>
                    </router-link>

                    <router-link to="/me" class="link" @click="closeUserMenu">
                        <svg
                            class="svgLinkIcon"
                            focusable="false"
                            aria-hidden="true"
                            viewBox="0 0 24 24"
                            data-testid="PersonIcon">
                            <path
                                d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4m0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4"
                            ></path>
                        </svg>
                        <span>{{ $t('hud.userMenu.account') }}</span>
                    </router-link>

                    <router-link to="/ranking" class="link" @click="closeUserMenu">
                        <svg
                            class="svgLinkIcon"
                            focusable="false"
                            aria-hidden="true"
                            viewBox="0 0 24 24"
                            data-testid="MilitaryTechIcon"
                        >
                            <path
                                d="M17 10.43V2H7v8.43c0 .35.18.68.49.86l4.18 2.51-.99 2.34-3.41.29 2.59 2.24L9.07 22 12 20.23 14.93 22l-.78-3.33 2.59-2.24-3.41-.29-.99-2.34 4.18-2.51c.3-.18.48-.5.48-.86m-4 1.8-1 .6-1-.6V3h2z"
                            ></path>
                        </svg>
                        <span>{{ $t('hud.userMenu.rankings') }}</span>
                    </router-link>

                    <router-link to="/news" class="link" @click="closeUserMenu">
                        <svg
                            class="svgLinkIcon"
                            focusable="false"
                            aria-hidden="true"
                            viewBox="0 0 24 24"
                            data-testid="InfoIcon">
                            <path
                                d="m23 12-2.44-2.78.34-3.68-3.61-.82-1.89-3.18L12 3 8.6 1.54 6.71 4.72l-3.61.81.34 3.68L1 12l2.44 2.78-.34 3.69 3.61.82 1.89 3.18L12 21l3.4 1.46 1.89-3.18 3.61-.82-.34-3.68zm-10 5h-2v-2h2zm0-4h-2V7h2z"
                            ></path>
                        </svg>
                        <span>{{ $t('hud.userMenu.news') }}</span>
                    </router-link>

                    <router-link to="/rules" class="link" @click="closeUserMenu">
                        <svg
                            class="svgLinkIcon"
                            focusable="false"
                            aria-hidden="true"
                            viewBox="0 0 24 24"
                            data-testid="InfoIcon">
                            <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4z"></path>
                            <path
                                d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-5.99 13c-.59 0-1.05-.47-1.05-1.05 0-.59.47-1.04 1.05-1.04.59 0 1.04.45 1.04 1.04-.01.58-.45 1.05-1.04 1.05m2.5-6.17c-.63.93-1.23 1.21-1.56 1.81-.13.24-.18.4-.18 1.18h-1.52c0-.41-.06-1.08.26-1.65.41-.73 1.18-1.16 1.63-1.8.48-.68.21-1.94-1.14-1.94-.88 0-1.32.67-1.5 1.23l-1.37-.57C11.51 5.96 12.52 5 13.99 5c1.23 0 2.08.56 2.51 1.26.37.61.58 1.73.01 2.57"
                            ></path>
                        </svg>
                        <span>{{ $t('hud.userMenu.rules') }}</span>
                    </router-link>

                    <div class="link" @click="logoutAndRedirectToHome">
                        <svg
                            class="svgLinkIcon logout"
                            focusable="false"
                            aria-hidden="true"
                            viewBox="0 0 24 24"
                            data-testid="InfoIcon"
                        >
                            <path
                                d="m17 7-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4z"
                            ></path>
                        </svg>
                        <span>{{ $t('hud.userMenu.logout') }}</span>
                    </div>
                </div>
                <div class="language">
                    <LocaleChange />
                </div>
            </div>
            <div class="notifications">
                <div class="section">
                    <span class="title">
                        <span>{{ $t('hud.userMenu.notifications.title') }}</span>
                    </span>
                    <button v-if="notifications.length > 0" @click="clearNotifications">{{ $t('hud.userMenu.readAll') }}</button>
                </div>

                <div v-for="(notification, index) in notifications" :key="index" class="notification">
                    <div class="element">
                        <span>{{ $t(notification) }}</span>
                    </div>
                    <div class="element">
                        <a class="go" @click="removeNotification(notification)">{{ $t('hud.userMenu.ok') }}</a>
                    </div>
                </div>
                <div v-if="notifications.length === 0" class="notification">
                    <div class="element">
                        <span>{{ $t('hud.userMenu.notifications.empty') }}</span>
                    </div>
                </div>
            </div>
            <div class="parameters">
                <span class="title">{{ $t('hud.userMenu.settings.name') }}</span>
                <div class="parameter" v-for="parameter in parameters" :key="parameter.name">
                    <svg
                        class="svgIcon"
                        focusable="false"
                        aria-hidden="true"
                        viewBox="0 0 24 24"
                        data-testid="InfoIcon">
                        <path :d="parameter.icon"></path>
                    </svg>
                    <span class="param">{{ $t(`hud.userMenu.settings.${parameter.name}`) }}</span>
                    <label class="switch">
                        <input type="checkbox" :checked="parameter.value" @change="store.dispatch(parameter.action)" />
                        <span class="slider round" />
                    </label>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useStore } from 'vuex';
import LocaleChange from '../Utils/LocaleChange.vue';
import { Settings } from '@/features/settings/store';
import router from '@/router';

const store = useStore();

// Computed properties
const userMenu = computed(() => store.getters['popup/userMenu']);
const username = computed(() => store.getters['auth/username']);
const notifications = computed(() => store.getters['notifications/notifications']);
const parameters = computed((): Settings[] => store.getters['settings/settings']);

// Methods
const closeUserMenu = () => store.dispatch('popup/closeUserMenu');
const logoutAndRedirectToHome = () => { store.dispatch('auth/logout'); router.push('/'); };
const removeNotification = (notification: string) => store.dispatch('notifications/removeNotification', notification);
const clearNotifications = () => store.dispatch('notifications/clearNotifications');
</script>

<style scoped lang="scss">
.indice {
	display: flex;
	flex-flow: wrap;
	-moz-box-pack: center;
	place-content: center;
	-moz-box-align: center;
	align-items: center;
	position: absolute;
	box-sizing: border-box;
	font-family: arial, sans-serif;
	font-weight: 500;
	font-size: 0.9rem;
	min-width: 20px;
	line-height: 1;
	padding: 0px 6px;
	height: 20px;
	border-radius: 10px;
	z-index: 1;
	transition: transform 225ms cubic-bezier(0.4, 0, 0.2, 1);
	background-color: rgb(46, 125, 50);
	color: rgb(255, 255, 255);
	top: 0px;
	right: 0px;
	transform: scale(1) translate(50%, -50%);
	transform-origin: 100% 0% 0px;
}
.notifications {
	display: flex;
	flex-direction: column;
	.section {
		background-color: rgb(15, 25, 36);
		display: flex;
		justify-content: space-between;
		margin-bottom: 8px;
		.title {
			box-sizing: border-box;
			list-style: none;
			color: rgba(255, 255, 255, 0.7);
			font-family: arial, sans-serif;
			font-weight: 500;
			padding: 8px 16px;
			position: sticky;
			top: 0px;
			z-index: 1;
			line-height: inherit;
		}
		button {
			display: inline-flex;
			-moz-box-align: center;
			align-items: center;
			-moz-box-pack: center;
			justify-content: center;
			position: relative;
			box-sizing: border-box;
			background-color: transparent;
			outline: 0px;
			border: 0px;
			margin: 0px;
			cursor: pointer;
			user-select: none;
			vertical-align: middle;
			appearance: none;
			text-decoration: none;
			font-family: arial, sans-serif;
			font-weight: 500;
			font-size: 1.1rem;
			line-height: 1.75;
			text-transform: uppercase;
			min-width: 64px;
			padding: 4px 5px;
			border-radius: 4px;
			transition:
				background-color 250ms cubic-bezier(0.4, 0, 0.2, 1),
				box-shadow 250ms cubic-bezier(0.4, 0, 0.2, 1),
				border-color 250ms cubic-bezier(0.4, 0, 0.2, 1),
				color 250ms cubic-bezier(0.4, 0, 0.2, 1);
			color: rgb(254, 125, 0);
			&:hover {
				text-decoration: none;
				background-color: rgba(219, 191, 149, 0.04);
			}
		}
	}

	.notification {
		transition: box-shadow 300ms cubic-bezier(0.4, 0, 0.2, 1);
		justify-content: space-between;
		gap: 5px;
		border-radius: 5px;
		margin: 0px 16px 8px;
		font-family: arial, sans-serif;
		font-weight: 400;
		line-height: 1.43;
		font-size: 1.2rem;
		background-color: transparent;
		display: flex;
		padding: 6px 12px;
		color: rgb(184, 231, 251);
		border: 1px solid rgb(79, 195, 247);
		box-shadow:
			rgba(0, 0, 0, 0.2) 0px 2px 1px -1px,
			rgba(0, 0, 0, 0.14) 0px 1px 1px 0px,
			rgba(0, 0, 0, 0.12) 0px 1px 3px 0px;
		.element {
			display: flex;
			padding: 8px 0px;
			min-width: 0px;
			overflow: auto;
			max-width: 70%;
			align-items: center;
			.go {
				display: inline-flex;
				-moz-box-align: center;
				align-items: center;
				-moz-box-pack: center;
				justify-content: center;
				position: relative;
				box-sizing: border-box;
				background-color: transparent;
				outline: 0px;
				margin: 0px;
				cursor: pointer;
				user-select: none;
				vertical-align: middle;
				appearance: none;
				text-decoration: none;
				font-family: arial, sans-serif;
				font-weight: 500;
				font-size: 1rem;
				line-height: 1.75;
				text-transform: uppercase;
				min-width: 64px;
				padding: 3px 9px;
				border-radius: 4px;
				transition:
					background-color 250ms cubic-bezier(0.4, 0, 0.2, 1),
					box-shadow 250ms cubic-bezier(0.4, 0, 0.2, 1),
					border-color 250ms cubic-bezier(0.4, 0, 0.2, 1),
					color 250ms cubic-bezier(0.4, 0, 0.2, 1);
				border: 1px solid rgba(2, 136, 209, 0.5);
				color: rgb(2, 136, 209);
				max-height: 25px;
				&:hover {
					text-decoration: none;
					background-color: rgba(2, 136, 209, 0.04);
					border: 1px solid rgb(2, 136, 209);
				}
			}
		}
	}
	.warning {
		border: 1px solid rgb(255, 183, 77);
		color: rgb(255, 226, 183);
		.element {
			.go {
				border: 1px solid rgba(255, 167, 38, 0.5);
				color: rgb(255, 167, 38);

				&:hover {
					text-decoration: none;
					background-color: rgba(255, 167, 38, 0.08);
					border: 1px solid rgb(255, 167, 38);
				}
			}
		}
	}
	.ban {
		border: 1px solid rgb(255, 77, 77);
		color: rgb(255, 183, 183);
		.element {
			.go {
				border: 1px solid rgba(255, 38, 38, 0.5);
				color: rgb(255, 38, 38);

				&:hover {
					text-decoration: none;
					background-color: rgba(255, 38, 38, 0.08);
					border: 1px solid rgb(255, 38, 38);
				}
			}
		}
	}
}
.shortCutMenu {
	padding: 16px;
	background-color: rgb(29, 32, 40);

	.shortCuts {
		display: flex;
		flex-direction: row;
		-moz-box-pack: center;
		justify-content: center;
		flex-wrap: wrap;
		gap: 1.5em;
		.link {
			display: flex;
			flex-direction: column;
			-moz-box-pack: center;
			justify-content: center;
			-moz-box-align: center;
			align-items: center;
			min-width: 64px;
			min-height: 64px;
			padding: 2px 2px;
			color: rgb(108, 113, 136);
			border-radius: 3px;
			text-decoration: none;
			cursor: pointer;
			span {
				margin: 0px;
				font-family: arial, sans-serif;
				font-weight: 400;
				font-size: 1rem;
				line-height: 1.66;
				text-align: center;
				word-wrap: break-word;
				max-width: 100%;
			}
			.svgLinkIcon {
				user-select: none;
				width: 1em;
				height: 1em;
				display: inline-block;
				fill: currentcolor;
				flex-shrink: 0;
				transition: fill 200ms cubic-bezier(0.4, 0, 0.2, 1);
				font-size: 2.8rem;
				color: rgb(254, 125, 0);
			}
			.admin {
				fill: currentcolor;
				color: rgb(2, 136, 209);
			}
			.logout {
				fill: currentcolor;
				color: rgb(211, 47, 47);
			}
			&:hover {
				color: rgb(255, 255, 255);
				background-color: rgb(254, 125, 0);
				box-shadow: rgb(189, 61, 0) 0px 0px 8px;
				svg {
					color: rgb(255, 255, 255);
				}
			}
		}
	}
	.language {
		display: flex;
		-moz-box-pack: center;
		justify-content: center;
		flex-wrap: wrap;
		gap: 8px;
		padding-top: 8px;
	}
}
.parameters {
	display: flex;
	flex-direction: column;
	.title {
		box-sizing: border-box;
		list-style: none;
		color: rgba(255, 255, 255, 0.7);
		font-family: arial, sans-serif;
		font-weight: 500;
		padding: 8px 16px;
		position: sticky;
		top: 0px;
		z-index: 1;
		background-color: rgb(15, 25, 36);
		line-height: inherit;
	}
	.parameter {
		display: flex;
		flex-direction: row;
		-moz-box-pack: start;
		justify-content: space-evenly;
		-moz-box-align: center;
		align-items: center;
		position: relative;
		text-decoration: none;
		width: 100%;
		box-sizing: border-box;
		text-align: left;
		padding: 4px 16px;
		.param {
			margin: 0px;
			font-family: arial, sans-serif;
			font-weight: 400;
			font-size: 1.2rem;
			line-height: 1.43;
			width: 202px;
			padding-left: 7px;
			display: block;
		}
		/* The switch - the box around the slider */
		.switch {
			position: relative;
			display: inline-block;
			width: 45px;
			height: 15px;
		}

		/* Hide default HTML checkbox */
		.switch input {
			opacity: 0;
			width: 0;
			height: 0;
		}

		/* The slider */
		.slider {
			position: relative;
			width: 45px;
			height: 15px;
			display: block;
			cursor: pointer;
			top: -20px;
			left: 0;
			right: 0;
			bottom: 0;
			background-color: #ccc;
			-webkit-transition: 0.4s;
			transition: 0.4s;
		}

		.slider:before {
			position: absolute;
			content: '';
			height: 20px;
			width: 20px;
			bottom: -2px;
			background-color: white;
			-webkit-transition: 0.4s;
			transition: 0.4s;
		}

		input:checked + .slider {
			background-color: $orange;
		}

		input:focus + .slider {
			box-shadow: 0 0 1px $orange;
		}

		input:checked + .slider:before {
			-webkit-transform: translateX(26px);
			-ms-transform: translateX(26px);
			transform: translateX(26px);
		}

		/* Rounded sliders */
		.slider.round {
			border-radius: 34px;
		}

		.slider.round:before {
			border-radius: 50%;
		}
	}
}
.svgIcon {
	user-select: none;
	width: 1em;
	height: 1em;
	display: inline-block;
	fill: currentcolor;
	flex-shrink: 0;
	transition: fill 200ms cubic-bezier(0.4, 0, 0.2, 1);
	font-size: 2rem;
}
.name {
	margin: 0px;
	font-size: 29px;
	letter-spacing: -0.24px;
	font-family: arial, sans-serif;
	line-height: 1.2;
	font-weight: 600;
	text-align: right;
	background-color: rgb(254, 125, 0);
	-moz-box-flex: 1;
	flex-grow: 1;
	padding-left: 16px;
	padding-right: 16px;
}
.burgerClose {
	display: inline-flex;
	-moz-box-align: center;
	align-items: center;
	-moz-box-pack: center;
	justify-content: center;
	position: relative;
	box-sizing: border-box;
	background-color: transparent;
	outline: 0px;
	border: 0px;
	margin: 0px;
	cursor: pointer;
	user-select: none;
	vertical-align: middle;
	appearance: none;
	text-decoration: none;
	text-align: center;
	flex: 0 0 auto;
	border-radius: 50%;
	overflow: visible;
	transition: background-color 150ms cubic-bezier(0.4, 0, 0.2, 1);
	padding: 5px;
	font-size: 1.2rem;
	color: rgb(255, 255, 255);
}
.close {
	display: flex;
	-moz-box-align: center;
	align-items: center;
	background-color: rgb(254, 181, 0);
}
.player {
	display: flex;
	flex-direction: row;
	-moz-box-align: stretch;
	align-items: stretch;
	color: rgb(255, 255, 255);
}
.root {
	box-shadow:
		rgba(0, 0, 0, 0.2) 0px 8px 10px -5px,
		rgba(0, 0, 0, 0.14) 0px 16px 24px 2px,
		rgba(0, 0, 0, 0.12) 0px 6px 30px 5px;
	overflow-y: auto;
	display: flex;
	flex-direction: column;
	height: 100%;
	flex: 1 0 auto;
	z-index: 1200;
	position: fixed;
	top: 0px;
	outline: 0px;
	right: 0px;
	width: 310px;
	background-color: rgb(17, 19, 23);
	color: rgb(183, 185, 198);
	background-image: none;
}
.backDrop {
	position: fixed;
	display: flex;
	-moz-box-align: center;
	align-items: center;
	-moz-box-pack: center;
	justify-content: center;
	inset: 0px;
	background-color: rgba(0, 0, 0, 0.5);
	z-index: 5;
	opacity: 1;
}

.slide-enter-active,
.slide-leave-active {
	transition: all 0.5s ease;
}

.slide-enter-from,
.slide-leave-to {
	transform: translateX(100%);
}

.slide-enter-to,
.slide-leave-from {
	transform: translateX(0);
}

.opacity-enter-active {
	transition: all 0.3s ease-out;
}

.opacity-enter-from,
.opacity-leave-to {
	opacity: 0;
}

.opacity-enter-to,
.opacity-leave-from {
	opacity: 1;
}
</style>
