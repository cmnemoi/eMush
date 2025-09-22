import { LocalStorageServiceInterface } from '@/shared/local.storage.service';
import { NotificationServiceInterface } from './notification.service';
import { Module } from "vuex";

export type NotificationState = {
    subscriptionStatus: boolean,
    notifications: string[],
    hasOpenedUserMenu: boolean,
};

export function createNotificationsModule(
    dependencies: {
        localStorageService: LocalStorageServiceInterface,
        notificationService: NotificationServiceInterface;
        translate: (key: string, params: Record<string, unknown>) => string;
    }
): Module<NotificationState, Record<string, any>> {
    const { localStorageService, notificationService, translate } = dependencies;

    return {
        namespaced: true,
        state: (): NotificationState => ({
            subscriptionStatus: localStorageService.getItemAsBoolean('subscriptionStatus'),
            hasOpenedUserMenu: localStorageService.getItemAsBoolean('hasOpenedUserMenu'),
            notifications: localStorageService.getItemAsBoolean('hasOpenedUserMenu') ? localStorageService.getItemAsArray('notifications')
                : [
                    'hud.userMenu.notifications.initialNotification'
                ]
        }),
        mutations: {
            setSubscriptionStatus(state: NotificationState, status: boolean) {
                state.subscriptionStatus = status;
            },
            setNotifications(state: NotificationState, notifications: string[]) {
                state.notifications = notifications;
            },
            setHasOpenedUserMenu(state: NotificationState, value: boolean) {
                state.hasOpenedUserMenu = value;
            }
        },
        getters: {
            isUserSubscribed: (state: NotificationState): boolean => state.subscriptionStatus,
            notifications: (state: NotificationState): string[] => state.notifications,
            notificationsCount: (state: NotificationState): number => state.notifications.length,
            hasOpenedUserMenu: (state: NotificationState): boolean => state.hasOpenedUserMenu
        },
        actions: {
            async subscribe({ commit, dispatch, rootState }): Promise<void> {
                try {
                    await notificationService.subscribe(rootState.auth.accessToken);
                    localStorageService.setItemAsBoolean('subscriptionStatus', true);
                    commit('setSubscriptionStatus', true);
                    dispatch('toast/openSuccessToast', translate('toast.notification.subscribeSuccess'), { root: true });
                } catch (error) {
                    localStorageService.setItemAsBoolean('subscriptionStatus', false);
                    dispatch('toast/openErrorToast', translate('toast.notification.subscribeError'), { root: true });
                    throw error;
                }
            },
            async unsubscribe({ commit, dispatch, rootState }): Promise<void> {
                try {
                    await notificationService.unsubscribe(rootState.auth.accessToken);
                    localStorageService.setItemAsBoolean('subscriptionStatus', false);
                    commit('setSubscriptionStatus', false);
                    dispatch('toast/openInfoToast', translate('toast.notification.unsubscribeSuccess'), { root: true });
                } catch (error) {
                    localStorageService.setItemAsBoolean('subscriptionStatus', true);
                    dispatch('toast/openErrorToast', translate('toast.notification.unsubscribeError'), { root: true });
                    console.error('Error during unsubscription:', error);
                    throw error;
                }
            },
            addNotification({ state, commit }, notification: string): void {
                const updatedNotifications = [notification, ...state.notifications];
                commit('setNotifications', updatedNotifications);
                localStorageService.saveItemAsArray('notifications', updatedNotifications);
            },
            removeNotification({ state, commit }, notification: string): void {
                const updatedNotifications = state.notifications.filter(notif => notif !== notification);
                commit('setNotifications', updatedNotifications);
                localStorageService.saveItemAsArray('notifications', updatedNotifications);
            },
            toggleNotificationSubscription({ dispatch, state }): void {
                state.subscriptionStatus ? dispatch('unsubscribe') : dispatch('subscribe');
            },
            clearNotifications({ commit }): void {
                commit('setNotifications', []);
                localStorageService.saveItemAsArray('notifications', []);
            },
            markUserMenuAsOpened({ commit, state }): void {
                if (state.hasOpenedUserMenu) {
                    return;
                }

                commit('setHasOpenedUserMenu', true);
                localStorageService.setItemAsBoolean('hasOpenedUserMenu', true);
            }
        }
    };
}
