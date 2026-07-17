type ModerationSanctionData = {
    id?: number;
    moderationAction?: string;
    reason?: string;
    message?: string;
    isActive?: boolean;
    startDate?: string;
    endDate?: string;
    user?: SanctionActorData;
    author?: SanctionActorData;
    sanctionEvidenceArray?: Parameters<SanctionEvidence["load"]>[0];
};

type SanctionActorData = {
    id: string;
    username: string;
    playerId: number | null;
    playerName: string | null;
};

export class SanctionActor {
    public id: string = '';
    public username: string = '';
    public playerId: number | null = null;
    public playerName: string | null = null;

    public load(object?: SanctionActorData): SanctionActor {
        if (object) {
            this.id = object.id;
            this.username = object.username;
            this.playerId = object.playerId;
            this.playerName = object.playerName;
        }
        return this;
    }
}

export class ModerationSanction {
    public id!: number;
    public user!: SanctionActor;
    public moderationAction!: string;
    public reason!: string;
    public message: string|null = null;
    public isActive!: boolean;
    public author!: SanctionActor;
    public isWarning!: boolean;
    public sanctionEvidence!: SanctionEvidence;
    public startDate!: Date;
    public endDate!: Date;

    public load(object: ModerationSanctionData): ModerationSanction {
        if (object) {
            this.id = object.id;
            this.moderationAction = object.moderationAction;
            this.reason = object.reason;
            this.message = object.message;
            this.isActive = object.isActive;
            this.startDate = new Date(object.startDate);
            this.endDate = new Date(object.endDate);
            this.user = new SanctionActor().load(object.user);
            this.author = new SanctionActor().load(object.author);
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

type SanctionEvidenceData = {
    className?: string;
    id?: number;
    message?: string;
    day?: number;
    cycle?: number;
    date?: string;
};

export class SanctionEvidence {
    public className: string = '';
    public id: number = 0;
    public message: string = '';
    public day: number = 0;
    public cycle: number = 0;
    public date: Date = new Date();

    public load(object: SanctionEvidenceData): SanctionEvidence {
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
        const data: object = {
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
