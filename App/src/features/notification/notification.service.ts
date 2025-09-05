import { WebPushClient } from "./web.push.client";
import urlJoin from "url-join";

const API_URL = import.meta.env.VITE_APP_API_URL;

export interface NotificationServiceInterface {
    subscribe(token: string): Promise<void>;
    unsubscribe(token: string): Promise<void>;
}

export class NotificationService implements NotificationServiceInterface {
    async subscribe(token: string): Promise<void> {
        await new WebPushClient({
            swPath: '@/sw.js',
            vapidPublicKey: import.meta.env.VITE_VAPID_PUBLIC_KEY,
            subscribeUrl: urlJoin(API_URL, 'notifications/subscribe'),
            unsubscribeUrl: urlJoin(API_URL, 'notifications/unsubscribe'),
            headers: {
                Authorization: `Bearer ${token}`
            }
        }).subscribe();
    }

    async unsubscribe(token: string): Promise<void> {
        await new WebPushClient({
            swPath: '@/sw.js',
            vapidPublicKey: import.meta.env.VITE_VAPID_PUBLIC_KEY,
            subscribeUrl: urlJoin(API_URL, 'notifications/subscribe'),
            unsubscribeUrl: urlJoin(API_URL, 'notifications/unsubscribe'),
            headers: {
                Authorization: `Bearer ${token}`
            }
        }).unsubscribe();
    }
}
