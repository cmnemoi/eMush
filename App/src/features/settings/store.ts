import { LocalStorageServiceInterface } from '@/shared/local.storage.service';
import { toArray } from "@/utils/toArray";
import { Module } from "vuex";

export type Settings = {
    name: string;
    icon: string;
    value: boolean;
    action: string;
};

export type SettingsState = {
    settings: Record<string, Settings>;
};

export function createSettingsModule(
    dependencies: {
        localStorageService: LocalStorageServiceInterface,
    }
): Module<SettingsState, Record<string, any>> {
    const { localStorageService } = dependencies;
    return {
        namespaced: true,
        state: (): SettingsState => ({
            settings: {
                subscribeToNotifications: {
                    name: 'subscribeToNotifications',
                    icon: 'M18.161 8.905A6.19 6.19 0 0 0 13.5 3.434V3a1.5 1.5 0 0 0-3 0v.434a6.19 6.19 0 0 0-4.661 5.47l-.253 2.033l-.001.015a4.34 4.34 0 0 1-1.357 2.807l-.014.012c-.244.23-.544.51-.73 1.058c-.17.496-.234 1.17-.234 2.186c0 .372.067.731.254 1.044c.193.324.472.524.76.646c.271.115.564.167.822.2c.174.022.372.039.562.055l.25.022q.345.033.742.065a.75.75 0 0 0-.3.777a3.7 3.7 0 0 0 .865 1.676A3.74 3.74 0 0 0 10 22.75c1.11 0 2.11-.484 2.795-1.25a.75.75 0 1 0-1.118-1c-.413.461-1.01.75-1.677.75a2.24 2.24 0 0 1-2.07-1.366a2 2 0 0 1-.125-.389a.75.75 0 0 0-.217-.38c1.213.077 2.696.135 4.412.135c2.622 0 4.703-.136 6.101-.268l.25-.022c.191-.016.389-.033.563-.055c.258-.033.55-.085.822-.2c.288-.122.567-.322.76-.646c.187-.313.254-.672.254-1.044c0-1.017-.064-1.69-.233-2.186c-.187-.548-.487-.829-.73-1.058l-.015-.012a4.34 4.34 0 0 1-1.357-2.807l-.001-.015zm-10.83.155l.001-.015a4.684 4.684 0 0 1 9.336 0l.001.015l.253 2.032a5.84 5.84 0 0 0 1.825 3.76c.226.213.288.279.35.46c.083.245.153.705.153 1.703c0 .201-.037.267-.041.274l-.003.004l-.002.002a.2.2 0 0 1-.054.03a1.7 1.7 0 0 1-.424.091c-.145.019-.292.031-.463.046l-.302.027c-1.357.127-3.39.261-5.961.261c-2.57 0-4.604-.134-5.96-.261l-.303-.027c-.171-.015-.318-.027-.463-.046a1.7 1.7 0 0 1-.424-.092a.2.2 0 0 1-.054-.029l-.005-.006c-.004-.007-.041-.073-.041-.274c0-.998.07-1.458.153-1.702c.062-.182.124-.248.35-.46a5.84 5.84 0 0 0 1.825-3.76z',
                    value: localStorageService.getItemAsBoolean('subscriptionStatus'),
                    action: 'notifications/toggleNotificationSubscription'
                },
                actionTabs: {
                    name: 'actionTabs',
                    icon: 'M2 8a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3zm11-1h6a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-6zm-2 0H5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h6z',
                    value: localStorageService.getItemAsBoolean('actionTabs'),
                    action: 'settings/toggleActionTabsVisibility'
                },
                doubleTap: {
                    name: 'doubleTap',
                    icon: 'M9.5 2a2.5 2.5 0 0 1 2.495 2.336L12 4.5v4.605l5.442.605a4 4 0 0 1 3.553 3.772l.005.203V14a8 8 0 0 1-7.75 7.996L13 22h-.674a8 8 0 0 1-7.024-4.171l-.131-.251l-2.842-5.684c-.36-.72-.093-1.683.747-2.028c1.043-.427 2.034-.507 3.055.012q.333.17.654.414l.215.17V4.5A2.5 2.5 0 0 1 9.5 2m0 2a.5.5 0 0 0-.492.41L9 4.5V13a1 1 0 0 1-1.78.625l-.332-.407l-.303-.354c-.58-.657-1.001-1.02-1.36-1.203a1.2 1.2 0 0 0-.694-.137l-.141.02l2.57 5.14a6 6 0 0 0 5.123 3.311l.243.005H13a6 6 0 0 0 5.996-5.775L19 14v-.315a2 2 0 0 0-1.621-1.964l-.158-.024l-5.442-.604a2 2 0 0 1-1.773-1.829L10 9.105V4.5a.5.5 0 0 0-.5-.5M4 6a1 1 0 0 1 0 2H3a1 1 0 0 1 0-2zm12-1a1 1 0 0 1 .117 1.993L16 7h-1a1 1 0 0 1-.117-1.993L15 5zM4.707 1.293l1 1a1 1 0 0 1-1.414 1.414l-1-1a1 1 0 0 1 1.414-1.414m11 0a1 1 0 0 1 0 1.414l-1 1a1 1 0 1 1-1.414-1.414l1-1a1 1 0 0 1 1.414 0',
                    value: localStorageService.getItemAsBoolean('doubleTap'),
                    action: 'settings/toggleDoubleTapVisibility'
                },
                lessPopups: {
                    name: 'lessPopups',
                    icon: 'M16 2H7.979C6.88 2 6 2.88 6 3.98V12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m0 10H8V4h8zM4 10H2v6c0 1.1.9 2 2 2h6v-2H4z',
                    value: localStorageService.getItemAsBoolean('lessPopups'),
                    action: 'settings/togglelessPopups'
                }
            }
        }),
        mutations: {
            setActionTabsVisibility(state: SettingsState, isVisible: boolean) {
                state.settings.actionTabs.value = isVisible;
                state.settings = { ...state.settings };
            },
            setDoubleTapVisibility(state: SettingsState, isVisible: boolean) {
                state.settings.doubleTap.value = isVisible;
                state.settings = { ...state.settings };
            },
            setlessPopupsVisibility(state: SettingsState, isVisible: boolean) {
                state.settings.lessPopups.value = isVisible;
                state.settings = { ...state.settings };
            }
        },
        getters: {
            settings: (state: SettingsState): Settings[] => toArray(state.settings),
            actionTabs: (state: SettingsState): boolean => state.settings.actionTabs.value,
            doubleTap: (state: SettingsState): boolean => state.settings.doubleTap.value,
            lessPopups: (state: SettingsState): boolean => state.settings.lessPopups.value
        },
        actions: {
            toggleActionTabsVisibility({ commit, state }): void {
                commit('setActionTabsVisibility', !state.settings.actionTabs.value);
                localStorageService.setItemAsBoolean('actionTabs', state.settings.actionTabs.value);
            },
            toggleDoubleTapVisibility({ commit, state }): void {
                commit('setDoubleTapVisibility', !state.settings.doubleTap.value);
                localStorageService.setItemAsBoolean('doubleTap', state.settings.doubleTap.value);
            },
            togglelessPopups({ commit, state }): void {
                commit('setlessPopupsVisibility', !state.settings.lessPopups.value);
                localStorageService.setItemAsBoolean('lessPopups', state.settings.lessPopups.value);
            }
        }
    };
}
