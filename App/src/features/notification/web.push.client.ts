export type WebPushClientOptions = {
  swPath: string; // e.g. "/sw.js" (must be at or above scope)
  vapidPublicKey: string; // URL-safe Base64, PUBLIC ONLY
  subscribeUrl: string; // backend endpoint to store subscription JSON
  unsubscribeUrl: string; // backend endpoint to delete subscription JSON
  headers?: Record<string, string>; // extra headers to send to backend
  withCredentials?: boolean; // if true, fetch() will include credentials
};

export class WebPushError extends Error {
    constructor(message: string) {
        super(message);
        this.name = "WebPushError";
    }
}

export class WebPushClient {
    private serviceWorkerPath: string;
    private vapidKey: string;
    private subscribeUrl: string;
    private unsubscribeUrl: string;
    private headers: Record<string, string>;
    private withCredentials: boolean;

    constructor(options: WebPushClientOptions) {
        this.serviceWorkerPath = options.swPath;
        this.vapidKey = options.vapidPublicKey;
        this.subscribeUrl = options.subscribeUrl;
        this.unsubscribeUrl = options.unsubscribeUrl;
        this.headers = options.headers ?? {};
        this.withCredentials = !!options.withCredentials;
    }

    /** Quick capability check (safe for SSR) */
    get isSupported(): boolean {
        if (typeof window === "undefined") return false;
        return "serviceWorker" in navigator && "PushManager" in window && "Notification" in window;
    }

    /** Ensure the Service Worker is registered and ready */
    private async areWeReadyToHandleNotifications(): Promise<ServiceWorkerRegistration> {
        if (!this.isSupported) throw new WebPushError("Web Push not supported in this environment");

        // If no active registration for the given scope, register now
        const existing = await navigator.serviceWorker.getRegistration();
        if (!existing || !existing.active) {
            await navigator.serviceWorker.register(this.serviceWorkerPath);
        }
        return navigator.serviceWorker.ready;
    }

    /** Get current subscription if any */
    public async getSubscription(): Promise<PushSubscription | null> {
        if (!this.isSupported) return null;
        const registration = await this.areWeReadyToHandleNotifications();
        return registration.pushManager.getSubscription();
    }

    /** Ask for Notification permission (idempotent). Call this from a user gesture. */
    public static async ensurePermission(): Promise<NotificationPermission> {
        if (typeof window === "undefined" || !("Notification" in window)) {
            throw new WebPushError("Notifications not available in this environment");
        }
        if (Notification.permission === "granted") return "granted";
        if (Notification.permission === "denied") throw new WebPushError("Notification permission previously denied");
        const permissionStatus = await Notification.requestPermission();
        if (permissionStatus !== "granted") throw new WebPushError("Notification permission not granted");
        return permissionStatus;
    }

    /** Subscribe user to push and send subscription to backend */
    public async subscribe(): Promise<PushSubscription> {
        const registration = await this.areWeReadyToHandleNotifications();

        // Already subscribed?
        let subscription = await registration.pushManager.getSubscription();
        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.vapidKey)
            });
        }

        // Persist subscription on backend
        await this.sendPostRequest(this.subscribeUrl, subscription);
        return subscription;
    }

    /** Unsubscribe user from push and inform backend */
    public async unsubscribe(): Promise<boolean> {
        const registration = await this.areWeReadyToHandleNotifications();
        const subscription = await registration.pushManager.getSubscription();
        if (!subscription) return false;

        try {
            await this.sendPostRequest(this.unsubscribeUrl, { endpoint: subscription.endpoint });
        } finally {
            // Ensure local unsubscribe even if backend call fails
            await subscription.unsubscribe();
        }
        return true;
    }

    private async sendPostRequest(url: string, payload: unknown): Promise<void> {
        const response = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json", ...this.headers },
            body: JSON.stringify(payload),
            credentials: this.withCredentials ? "include" : undefined
        });
        if (!response.ok) {
            const text = await response.text().catch(() => "");
            throw new WebPushError(`Backend ${url} responded ${response.status}: ${text}`);
        }
    }

    private urlBase64ToUint8Array(base64String: string): Uint8Array {
        const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
        const raw = typeof atob !== "undefined" ? atob(base64) : Buffer.from(base64, "base64").toString("binary");
        const output = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; ++i) output[i] = raw.charCodeAt(i);
        return output;
    }
}

