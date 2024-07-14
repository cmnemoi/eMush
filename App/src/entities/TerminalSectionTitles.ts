export class TerminalSectionTitles {
    public orientateDaedalus: string|null = null;
    public moveDaedalus: string|null = null;
    public generalInformations: string|null = null;
    public pilgred: string|null = null;
    public orientation: string|null = null;
    public distance: string|null = null;
    public cpuPriorityName: string|null = null;
    public cpuPriorityDescription: string|null = null;
    public crewLockName: string|null = null;
    public crewLockDescription: string|null = null;
    public plasmaShieldName: string|null = null;
    public plasmaShieldDescription: string|null = null;

    public load(object: any): TerminalSectionTitles {
        if (object) {
            this.orientateDaedalus = object['orientate_daedalus'];
            this.moveDaedalus = object['move_daedalus'];
            this.generalInformations = object['general_informations'];
            this.pilgred = object['pilgred'];
            this.orientation = object['orientation'];
            this.distance = object['distance'];
            this.cpuPriorityName = object['cpu_priority_name'];
            this.cpuPriorityDescription = object['cpu_priority_description'];
            this.crewLockName = object['crew_lock_name'];
            this.crewLockDescription = object['crew_lock_description'];
            this.plasmaShieldName = object['plasma_shield_name'];
            this.plasmaShieldDescription = object['plasma_shield_description'];
        }
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: any): TerminalSectionTitles {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
