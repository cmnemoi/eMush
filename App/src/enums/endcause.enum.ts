import { getImgUrl } from "@/utils/getImgUrl";

export enum EndCauseEnum {
    EDEN = 'eden',
    EDEN_INFECTED = 'eden_infected',
    KILLED_BY_NERON = 'killed_by_neron',
    SOL_RETURN = 'sol_return',
    SOL_RETURN_INFECTED = 'sol_return_infected',
    DAEDALUS_DESTROYED = 'daedalus_destroyed',
}

export const EndCauseConfig: {[index: string]: {img: string; short_name: string;}} = {
    [EndCauseEnum.EDEN]: { img: getImgUrl('ending-eden.png'), short_name: 'theEnd.endCause.eden' },
    [EndCauseEnum.EDEN_INFECTED]: { img: getImgUrl('ending-eden-infected.png'), short_name: 'theEnd.endCause.eden_infected' },
    [EndCauseEnum.KILLED_BY_NERON]: { img: getImgUrl('ending-neron.png'), short_name: 'theEnd.endCause.killed_by_neron' },
    [EndCauseEnum.SOL_RETURN]: { img: getImgUrl('ending-sol.png'), short_name: 'theEnd.endCause.sol_return' },
    [EndCauseEnum.SOL_RETURN_INFECTED]: { img: getImgUrl('ending-sol-infected.png'), short_name: 'theEnd.endCause.sol_return_infected' },
    [EndCauseEnum.DAEDALUS_DESTROYED]: { img: getImgUrl('ending-destroyed.png'), short_name: 'theEnd.endCause.destroyed' }
};

export function getEndCauseConfig(endCause: EndCauseEnum|null): {img: string; short_name: string} {
    if (endCause === null)
    {return EndCauseConfig[EndCauseEnum.DAEDALUS_DESTROYED];}
    return EndCauseConfig[endCause] ?? EndCauseConfig[EndCauseEnum.DAEDALUS_DESTROYED];
}
