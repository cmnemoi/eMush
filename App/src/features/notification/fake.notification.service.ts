import { NotificationServiceInterface } from "./notification.service";

export class FakeNotificationService implements NotificationServiceInterface {
    isUserSubscribed = false;
    throw = false;

    async subscribe(): Promise<void> {
        if (this.throw) {
            throw new Error("Fake notification service error");
        }

        this.isUserSubscribed = true;
    }

    async unsubscribe(): Promise<void> {
        if (this.throw) {
            throw new Error("Fake notification service error");
        }

        this.isUserSubscribed = false;
    }

    shouldThrow() {
        this.throw = true;
    }
}
