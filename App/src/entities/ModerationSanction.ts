import * as string_decoder from "node:string_decoder";

export class ModerationSanction {
    public userId!: string;
    public username!: string;
    public moderationAction!: string;
    public reason!: string;
    public message: string|null = null;
    public isActive!: boolean;
    public startDate!: Date;
    public endDate!: Date;
    public authorName!: string;
    public isWarning!: boolean;
    public playerId!: number;
    public playerName!: string;
    public sanctionEvidence!: SanctionEvidence

    public load(object: any): ModerationSanction {
        if (object) {
            this.moderationAction = object.moderationAction;
            this.reason = object.reason;
            this.message = object.message;
            this.isActive = object.isActive;
            this.startDate = new Date(object.startDate).toLocaleString("en-US", {
                weekday: "short",
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "numeric"
            });
            this.endDate = new Date(object.endDate).toLocaleString("en-US", {
                weekday: "short",
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "numeric"
            });
            this.authorName = object.authorName;
            this.playerId = object.playerId;
            this.playerName = object.playerName;
            this.userId = object.userId;
            this.username = object.username;
            this.isWarning = object.moderationAction === 'warning';
            this.sanctionEvidence = new SanctionEvidence().load(object.sanctionEvidenceArray);
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

export class SanctionEvidence {
    public className: string;
    public id: number;
    public message: string;

    public load(object: any): SanctionEvidence {
        if (object) {
            this.className = object.className;
            this.id = object.id;
            this.message = object.message;
        }
        return this;
    }

    jsonEncode() : object {
        const data: any = {
            'id': this.id,
            'className': this.className,
            'message': this.message,
        };
    }
}
