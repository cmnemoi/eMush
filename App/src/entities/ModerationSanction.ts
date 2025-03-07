import * as string_decoder from "node:string_decoder";

export class ModerationSanction {
    public id!: number;
    public userId!: string;
    public username!: string;
    public moderationAction!: string;
    public reason!: string;
    public message: string|null = null;
    public isActive!: boolean;
    public authorName!: string;
    public isWarning!: boolean;
    public playerId!: number;
    public playerName!: string;
    public sanctionEvidence!: SanctionEvidence;
    public startDate!: Date;
    public endDate!: Date;

    public load(object: any): ModerationSanction {
        if (object) {
            this.id = object.id;
            this.moderationAction = object.moderationAction;
            this.reason = object.reason;
            this.message = object.message;
            this.isActive = object.isActive;
            this.startDate = new Date(object.startDate);
            this.endDate = new Date(object.endDate);
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

    public startDateGivenLocale(locale: string): string {
        return this.startDate.toLocaleDateString(locale, {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "numeric"
        });
    }

    public endDateGivenLocale(locale: string): string {
        return this.endDate.toLocaleDateString(locale, {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "numeric"
        });
    }
}

export class SanctionEvidence {
    public className: string = '';
    public id: number = 0;
    public message: string = '';
    public day: number = 0;
    public cycle: number = 0;
    public date: Date = new Date();

    public load(object: any): SanctionEvidence {
        if (object) {
            this.className = object.className;
            this.id = object.id;
            this.message = object.message;
            this.day = object.day;
            this.cycle = object.cycle;
            this.date = new Date(object.date);
        }
        return this;
    }

    jsonEncode() : object {
        const data: any = {
            'id': this.id,
            'className': this.className,
            'message': this.message,
            'cycle': this.cycle,
            'day': this.day,
            'date': this.date
        };

        return data;
    }
}
