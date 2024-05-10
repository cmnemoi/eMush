export class ModerationSanction {
    public moderationAction!: string;
    public reason!: string;
    public message: string|null = null;
    public isActive!: boolean;
    public startDate!: Date;
    public endDate!: Date;
    public isWarning!: boolean;

    public load(object: any): ModerationSanction {
        if (object) {
            this.moderationAction = object.moderationAction;
            this.reason = object.reason;
            this.message = object.message;
            this.isActive = object.isActive;
            this.startDate = new Date(object.startDate);
            this.endDate = new Date(object.endDate);
            this.isWarning = object.moderationAction === 'warning';
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): ModerationSanction {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
