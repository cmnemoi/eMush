import { AdvanceDaedalusStatus } from "@/entities/AdvanceDaedalusStatus";
import { Planet } from "./Planet";

export class TerminalInfos {
    public requirements: string[];
    public difficulty: string|null;
    public advanceDaedalusStatus: AdvanceDaedalusStatus|null;
    public daedalusOrientation: string|null;
    public planets: Planet[]|null;
    public maxDiscoverablePlanets: number|null;
    public inOrbit: string|null;
    public availableCpuPriorities: {key: string, name: string}[]|null;
    public currentCpuPriority: string|null;
    public pilgredIsFinished: boolean|null = null;
    public pilgredFinishedDescription: string|null = null;
    public noProposedNeronProjects: boolean|null = null;
    public noProposedNeronProjectsDescription: string|null = null;
    public availableCrewLocks: {key: string, name: string}[]|null = null;
    public currentCrewLock: string|null = null;
    public availableFoodDestructionOptions: {key: string, name: string}[]|null = null;
    public currentFoodDestructionOption: string|null = null;
    public plasmaShieldToggles: {key: string, name: string}[]|null = null;
    public isPlasmaShieldActive: boolean|null = null;
    public magneticNetToggles: {key: string, name: string}[]|null = null;
    public isMagneticNetActive: boolean|null = null;
    public neronInhibitionToggles: {key: string, name: string}[]|null = null;
    public isNeronInhibited: boolean|null = null;
    public nothingToCompute: string|null = null;
    public edenComputed: string|null = null;
    public linkStrength: string|null = null;
    public linkEstablished: string|null = null;
    public neronUpdateStatus: string|null = null;
    public selectRebelBaseToDecode: string|null = null;
    public cannotTradeUnderAttack: boolean|null = null;
    public never: string|null = null;
    public seeCommunications: string|null = null;
    public seeTrades: string|null = null;
    public areVocodedAnnouncementsActive: boolean|null = null;
    public vocodedAnnouncementsToggles: {key: string, name: string}[]|null = null;
    public areDeathAnnouncementsActive: boolean|null = null;
    public deathAnnouncementsToggles: {key: string, name: string}[]|null = null;

    constructor() {
        this.requirements = [];
        this.difficulty = null;
        this.advanceDaedalusStatus = null;
        this.daedalusOrientation = null;
        this.planets = null;
        this.maxDiscoverablePlanets = null;
        this.inOrbit = null;
        this.availableCpuPriorities = null;
        this.currentCpuPriority = null;
    }

    public load(object: any): TerminalInfos {
        if (object) {
            this.requirements = object.requirements;
            this.difficulty = object.difficulty;
            this.advanceDaedalusStatus = new AdvanceDaedalusStatus().load(object.advanceDaedalusStatus);
            this.daedalusOrientation = object.orientation;
            this.planets = object.planets?.map((planet: any) => new Planet().load(planet));
            this.maxDiscoverablePlanets = object.maxDiscoverablePlanets;
            this.inOrbit = object.inOrbit;
            this.availableCpuPriorities = object.availableCpuPriorities;
            this.currentCpuPriority = object.currentCpuPriority;
            this.pilgredIsFinished = object.pilgredIsFinished;
            this.pilgredFinishedDescription = object.pilgredFinishedDescription;
            this.noProposedNeronProjects = object.noProposedNeronProjects;
            this.noProposedNeronProjectsDescription = object.noProposedNeronProjectsDescription;
            this.availableCrewLocks = object.crewLocks;
            this.currentCrewLock = object.currentCrewLock;
            this.availableFoodDestructionOptions = object.foodDestructionOptions;
            this.currentFoodDestructionOption = object.currentFoodDestructionOption;
            this.plasmaShieldToggles = object.plasmaShieldToggles;
            this.isPlasmaShieldActive = object.isPlasmaShieldActive;
            this.magneticNetToggles = object.magneticNetToggles;
            this.isMagneticNetActive = object.isMagneticNetActive;
            this.neronInhibitionToggles = object.neronInhibitionToggles;
            this.isNeronInhibited = object.isNeronInhibited;
            this.nothingToCompute = object.nothingToCompute;
            this.edenComputed = object.edenComputed;
            this.linkStrength = object.linkStrength;
            this.linkEstablished = object.linkEstablished;
            this.neronUpdateStatus = object.neronUpdateStatus;
            this.selectRebelBaseToDecode = object.selectRebelBaseToDecode;
            this.cannotTradeUnderAttack = object.cannotTradeUnderAttack;
            this.never = object.never;
            this.seeCommunications = object.seeCommunications;
            this.seeTrades = object.seeTrades;
            this.areVocodedAnnouncementsActive = object.areVocodedAnnouncementsActive;
            this.vocodedAnnouncementsToggles = object.vocodedAnnouncementsToggles;
            this.areDeathAnnouncementsActive = object.areDeathAnnouncementsActive;
            this.deathAnnouncementsToggles = object.deathAnnouncementsToggles;
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
