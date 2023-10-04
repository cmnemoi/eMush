export class AdvanceDaedalusStatus {

    static readonly EMERGENCY_REACTOR_BROKEN = "emergency_reactor_broken";
    static readonly INFO = "info";
    static readonly WARNING = "warning";

    public key!: string;
    public text!: string;
    public type!: string;

    public load(object: any): AdvanceDaedalusStatus {
        if (object) {
            this.key = object.key;
            this.text = object.text;
            this.type = this.key === AdvanceDaedalusStatus.EMERGENCY_REACTOR_BROKEN ? AdvanceDaedalusStatus.WARNING : AdvanceDaedalusStatus.INFO;
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): AdvanceDaedalusStatus {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

    public isWarning(): boolean {
        return this.type === AdvanceDaedalusStatus.WARNING;
    }
}