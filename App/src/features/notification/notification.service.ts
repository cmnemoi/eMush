import { WebPushClient } from "./web.push.client";
import urlJoin from "url-join";

const API_URL = import.meta.env.VITE_APP_API_URL;

export interface NotificationServiceInterface {
    subscribe(): Promise<void>;
    unsubscribe(): Promise<void>;
}

export class NotificationService implements NotificationServiceInterface {
    async subscribe(): Promise<void> {
        await new WebPushClient({
            swPath: '@/sw.js',
            vapidPublicKey: import.meta.env.VITE_VAPID_PUBLIC_KEY,
            subscribeUrl: urlJoin(API_URL, 'notifications/subscribe'),
            unsubscribeUrl: urlJoin(API_URL, 'notifications/unsubscribe'),
            withCredentials: true
        }).subscribe();
    }

    async unsubscribe(): Promise<void> {
        await new WebPushClient({
            swPath: '@/sw.js',
            vapidPublicKey: import.meta.env.VITE_VAPID_PUBLIC_KEY,
            subscribeUrl: urlJoin(API_URL, 'notifications/subscribe'),
            unsubscribeUrl: urlJoin(API_URL, 'notifications/unsubscribe'),
            withCredentials: true
        }).unsubscribe();
    }
}
