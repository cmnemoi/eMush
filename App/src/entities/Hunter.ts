import { Action } from "./Action";
import {
    HUNTER_COLUMN_COUNT,
    HUNTER_COLUMN_SIZE,
    HunterImageKeyEnum,
    HunterKeyEnum,
    HunterRankEnum
} from "@/enums/hunter.enum";

export class Hunter {
    public id!: number;
    public key!: HunterKeyEnum;
    public name!: string;
    public description!: string;
    public health!: integer;
    public charges: integer | null;
    public actions: Array<Action>;
    public transportImage: HunterImageKeyEnum | null;

    constructor() {
        this.charges = null;
        this.actions = new Array<Action>();
        this.transportImage = null;
    }

    public load(object: any): Hunter {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.description = object.description;
            this.health = object.health;
            this.charges = object.charges;
            object.actions.forEach((actionObject: any) => {
                this.actions.push((new Action).load(actionObject));
            });
            this.transportImage = object.transportImage;
        }

        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString: string): Hunter {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}


export class HunterGroup {
    public hunters: Array<Hunter>;

    constructor(hunter: Hunter) {
        this.hunters = [hunter];
    }

    /**
     * Takes a list of hunters and groups them by key and health.
     *
     * Hunters are associated with arbitrary ranks (see `HunterRankEnum`) where
     * the higher the rank, the more "dangerous" the hunter is.
     *
     * This method will try to group hunters by their key and health, then sort
     * them by descending rank then ascending health, i.e., the group of hunters
     * with the lowest rank and the most HP be displayed first (top right).
     *
     * It will then try to unwrap groups starting from the highest rank and the
     * lowest health until reaching `HUNTER_COLUMN_SIZE * HUNTER_COLUMN_COUNT`
     * groups. This means that the most dangerous and hurt hunters will be
     * unwrapped first.
     */
    public static fromHunterArray(hunters: Array<Hunter> | undefined): Array<HunterGroup> {
        if (!hunters) return [];

        // Regroup hunters by key and health
        const groupsMapping = new Map<string, HunterGroup>();
        for (const hunter of hunters) {
            const key = `${hunter.key}-${hunter.health}`;
            if (!groupsMapping.has(key)) {;
                groupsMapping.set(key, new HunterGroup(hunter));
            } else {
                groupsMapping.get(key)?.hunters.push(hunter);
            }
        }

        // Convert groups to list and sort them by the descending rank and ascending health of the hunter they represent
        const groups = Array.from(groupsMapping.values());
        groups.sort((a, b) => {
            const rankDiff = HunterRankEnum[b.hunters[0].key] - HunterRankEnum[a.hunters[0].key];
            if (rankDiff !== 0) return rankDiff;
            return a.hunters[0].health - b.hunters[0].health;
        });

        // While we have less than two columns of hunters, unwrap hunters starting from the lowest rank.
        let i = 0;
        while (groups.length < HUNTER_COLUMN_SIZE * HUNTER_COLUMN_COUNT && i < groups.length) {
            if (groups[i].length() > 1) {
                while (groups.length < HUNTER_COLUMN_SIZE * HUNTER_COLUMN_COUNT && groups[i].length() > 1) {
                    groups.push(new HunterGroup(<Hunter> groups[i].hunters.pop()));
                }
            }
            i++;
        }

        // Sort the list by ascending rank and descending health (the order used to display them)
        groups.sort((a, b) => {
            const rankDiff = HunterRankEnum[a.hunters[0].key] - HunterRankEnum[b.hunters[0].key];
            if (rankDiff !== 0) return rankDiff;
            return b.hunters[0].health - a.hunters[0].health;
        });

        return groups;
    }

    public length(): number {
        return this.hunters.length;
    }
}
