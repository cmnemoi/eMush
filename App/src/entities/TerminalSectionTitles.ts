export class TerminalSectionTitles {
    public orientateDaedalus: string|null = null;
    public moveDaedalus: string|null = null;
    public generalInformations: string|null = null;
    public pilgred: string|null = null;
    public orientation: string|null = null;
    public distance: string|null = null;
    public cpuPriorityName: string|null = null;
    public cpuPriorityDescription: string|null = null;
    public foodDestructionOptionName: string|null = null;
    public foodDestructionOptionDescription: string|null = null;
    public crewLockName: string|null = null;
    public crewLockDescription: string|null = null;
    public plasmaShieldName: string|null = null;
    public plasmaShieldDescription: string|null = null;
    public magneticNetName: string|null = null;
    public magneticNetDescription: string|null = null;
    public neronInhibitionName: string|null = null;
    public neronInhibitionDescription: string|null = null;
    public toANewEdenTitle: string|null = null;
    public toANewEdenDescription: string|null = null;
    public contact: string|null = null;
    public neronVersion: string|null = null;
    public rebelBasesNetwork: string|null = null;
    public xylophDb: string|null = null;
    public vocodedAnnouncementName: string|null = null;
    public vocodedAnnouncementDescription: string|null = null;
    public deathAnnouncementName: string|null = null;
    public deathAnnouncementDescription: string|null = null;

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
            this.foodDestructionOptionName = object['food_destruction_option_name'];
            this.foodDestructionOptionDescription = object['food_destruction_option_description'];
            this.crewLockName = object['crew_lock_name'];
            this.crewLockDescription = object['crew_lock_description'];
            this.plasmaShieldName = object['plasma_shield_name'];
            this.plasmaShieldDescription = object['plasma_shield_description'];
            this.magneticNetName = object['magnetic_net_name'];
            this.magneticNetDescription = object['magnetic_net_description'];
            this.neronInhibitionName = object['neron_inhibition_name'];
            this.neronInhibitionDescription = object['neron_inhibition_description'];
            this.toANewEdenTitle = object['to_a_new_eden_title'];
            this.toANewEdenDescription = object['to_a_new_eden_description'];
            this.contact = object['contact'];
            this.neronVersion = object['neron_version'];
            this.rebelBasesNetwork = object['rebel_bases_network'];
            this.xylophDb = object['xyloph_db'];
            this.vocodedAnnouncementName = object['vocoded_announcements_name'];
            this.vocodedAnnouncementDescription = object['vocoded_announcements_description'];
            this.deathAnnouncementName = object['death_announcements_name'];
            this.deathAnnouncementDescription = object['death_announcements_description'];
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
