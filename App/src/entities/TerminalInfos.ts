import { AdvanceDaedalusStatus } from "@/entities/AdvanceDaedalusStatus";

export class TerminalInfos {
    public difficulty: string|null;
    public advanceDaedalusStatus: AdvanceDaedalusStatus|null;

    constructor() {
        this.difficulty = null;
        this.advanceDaedalusStatus = null;
    }

    public load(object: any): TerminalInfos {
        if (object) {
            this.difficulty = object.difficulty;
            this.advanceDaedalusStatus = new AdvanceDaedalusStatus().load(object.advanceDaedalusStatus);
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): TerminalInfos {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }

}