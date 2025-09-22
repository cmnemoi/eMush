import { beforeEach, describe, it, expect } from "vitest";

import { FakeNotificationService } from "./fake.notification.service";
import { FakeLocalStorageService } from "@/shared/fake.local.storage.service";
import { createStore, Store } from "vuex";
import { createNotificationsModule } from "./store";
import { toast as toastModule } from "@/store/toast.module";
import { LocalStorageServiceInterface } from "@/shared/local.storage.service";
import { NotificationServiceInterface } from "./notification.service";

describe("Notification Store", () => {
    let store: Store<Record<string, any>>;
    let localStorageService: LocalStorageServiceInterface;
    let notificationService: NotificationServiceInterface;
    beforeEach(() => {
        localStorageService = new FakeLocalStorageService();
        localStorageService.setItemAsBoolean('hasOpenedUserMenu', true);
        notificationService = new FakeNotificationService();
        store = createStore({
            modules: {
                notifications: createNotificationsModule({
                    localStorageService,
                    notificationService,
                    translate: (key: string) => key
                }),
                toast: toastModule,
                auth: { state: { accessToken: 'token' } }
            }
        });
    });

    it("should set subscription status to false by default", () => {
        expect(store.getters["notifications/isUserSubscribed"]).toBe(false);
    });

    it("should set notifications from local storage at start", () => {
        localStorageService.saveItemAsArray('notifications', ["notification1", "notification2"]);
        store = createStore({
            modules: {
                notifications: createNotificationsModule({ localStorageService, notificationService, translate: (key: string) => key }),
                toast: toastModule
            }
        });

        expect(store.getters["notifications/notifications"]).toEqual(["notification1", "notification2"]);
    });

    it("should subscribe user to notifications", async () => {
        await store.dispatch("notifications/subscribe");

        expect(store.getters["notifications/isUserSubscribed"]).toBe(true);
        expect(notificationService.isUserSubscribed).toBe(true);
        expect(localStorageService.getItemAsBoolean('subscriptionStatus')).toBe(true);
    });

    it("should not subscribe on subscription error", async () => {
        notificationService.shouldThrow();

        await expect(store.dispatch("notifications/subscribe")).rejects.toThrow();

        expect(store.getters["notifications/isUserSubscribed"]).toBe(false);
        expect(notificationService.isUserSubscribed).toBe(false);
        expect(localStorageService.getItemAsBoolean('subscriptionStatus')).toBe(false);
    });

    it("should display success toast on subscription", async () => {
        await store.dispatch("notifications/subscribe");

        expect(store.getters["toast/toast"]).toEqual({
            isOpen: true,
            title: "toast.notification.subscribeSuccess",
            type: "success"
        });
    });

    it("should display error toast on subscription error", async () => {
        notificationService.shouldThrow();

        await expect(store.dispatch("notifications/subscribe")).rejects.toThrow();

        expect(store.getters["toast/toast"]).toEqual({
            isOpen: true,
            title: "toast.notification.subscribeError",
            type: "error"
        });
    });

    it("should add notification to store", async () => {
        const notification = "notification";

        await store.dispatch("notifications/addNotification", notification);

        expect(store.getters["notifications/notifications"]).toEqual(["notification"]);
        expect(store.getters["notifications/notificationsCount"]).toBe(1);
        expect(localStorageService.getItemAsArray('notifications')).toEqual(["notification"]);
    });

    it("should remove notification from store", async () => {
        const notification = "notification";
        await store.dispatch("notifications/addNotification", notification);

        await store.dispatch("notifications/removeNotification", notification);

        expect(store.getters["notifications/notifications"]).toEqual([]);
        expect(store.getters["notifications/notificationsCount"]).toBe(0);
        expect(localStorageService.getItemAsArray('notifications')).toEqual([]);
    });

    it("should clear all notifications from store", async () => {
        await store.dispatch("notifications/addNotification", "notification1");
        await store.dispatch("notifications/addNotification", "notification2");

        await store.dispatch("notifications/clearNotifications");

        expect(store.getters["notifications/notifications"]).toEqual([]);
        expect(store.getters["notifications/notificationsCount"]).toBe(0);
        expect(localStorageService.getItemAsArray('notifications')).toEqual([]);
    });

    it("should unsubscribe user from notifications", async () => {
        await store.dispatch("notifications/subscribe");
        expect(store.getters["notifications/isUserSubscribed"]).toBe(true);

        await store.dispatch("notifications/unsubscribe");

        expect(store.getters["notifications/isUserSubscribed"]).toBe(false);
        expect(notificationService.isUserSubscribed).toBe(false);
        expect(localStorageService.getItemAsBoolean('subscriptionStatus')).toBe(false);
    });

    it("should display info toast on unsubsription", async () => {
        await store.dispatch("notifications/subscribe");

        await store.dispatch("notifications/unsubscribe");

        expect(store.getters["toast/toast"]).toEqual({
            isOpen: true,
            title: "toast.notification.unsubscribeSuccess",
            type: "info"
        });
    });

    it("should toggle notification subscription", async () => {
        await store.dispatch("notifications/subscribe");

        expect(store.getters["notifications/isUserSubscribed"]).toBe(true);
        expect(localStorageService.getItemAsBoolean('subscriptionStatus')).toBe(true);

        await store.dispatch("notifications/toggleNotificationSubscription");

        expect(store.getters["notifications/isUserSubscribed"]).toBe(false);
        expect(localStorageService.getItemAsBoolean('subscriptionStatus')).toBe(false);
    });

    it("should mark user menu as opened", async () => {
        localStorageService.setItemAsBoolean('hasOpenedUserMenu', false);
        store = createStore({
            modules: {
                notifications: createNotificationsModule({
                    localStorageService,
                    notificationService,
                    translate: (key: string) => key
                }),
                toast: toastModule,
                auth: { state: { accessToken: 'token' } }
            }
        });

        await store.dispatch("notifications/markUserMenuAsOpened");

        expect(store.getters["notifications/hasOpenedUserMenu"]).toBe(true);
        expect(localStorageService.getItemAsBoolean('hasOpenedUserMenu')).toBe(true);
    });

    it("should init state when user menu has not been opened", async () => {
        localStorageService.setItemAsBoolean('hasOpenedUserMenu', false);
        store = createStore({
            modules: {
                notifications: createNotificationsModule({
                    localStorageService,
                    notificationService,
                    translate: (key: string) => key
                }),
                toast: toastModule,
                auth: { state: { accessToken: 'token' } }
            }
        });

        expect(store.state.notifications).toEqual({
            subscriptionStatus: false,
            hasOpenedUserMenu: false,
            notifications: [
                'hud.userMenu.notifications.initialNotification'
            ]
        });
    });
});
