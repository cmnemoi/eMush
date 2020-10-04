import {LogEnum} from "../../src/enums/log.enum";

export default {
    [LogEnum.AWAKEN]: [
        {
            message: "**{character}** se réveille doucement de son long sommeil.",
            weighting: 1
        },
    ],
    [LogEnum.NEW_DAY]: [
        {
            message: "Un jour de plus à lutter...Votre moral baisse...",
            weighting: 1
        },
    ],
    [LogEnum.GAIN_ACTION_POINT]: [
        {
            message: " Vous avez gagné {number} :pa:. ",
            weighting: 1
        }
    ],
    [LogEnum.LOSS_ACTION_POINT]: [
        {
            message: " Vous avez perdu {number} :pa:. ",
            weighting: 1
        }
    ],
    [LogEnum.GAIN_MOVEMENT_POINT]: [
        {
            message: " Vous avez gagné {number} :pm:. ",
            weighting: 1
        }
    ],
    [LogEnum.LOSS_MOVEMENT_POINT]: [
        {
            message: " Vous avez perdu {number} :pm:. ",
            weighting: 1
        }
    ],
    [LogEnum.GAIN_HEALTH_POINT]: [
        {
            message: " Vous avez gagné {number} :hp:. ",
            weighting: 1
        }
    ],
    [LogEnum.LOSS_HEALTH_POINT]: [
        {
            message: " Vous avez perdu {number} :hp:. ",
            weighting: 1
        }
    ],
    [LogEnum.GAIN_MORAL_POINT]: [
        {
            message: " Vous avez gagné {number} :mp:. ",
            weighting: 1
        }
    ],
    [LogEnum.LOSS_MORAL_POINT]: [
        {
            message: " Vous avez perdu {number} :mp:. ",
            weighting: 1
        }
    ],
    [LogEnum.ENTER_ROOM]: [
        {
            message: "**{character}** est entré.",
            weighting: 1
        },
    ],
    [LogEnum.EXIT_ROOM]: [
        {
            message: "**{character}** est sorti.",
            weighting: 1
        }
    ],
    [LogEnum.EAT]: [
        {
            message: "**{character}** a dévoré sa ration.",
            weighting: 2500
        },
        {
            message: "**{character}** s'est bien callé avec sa petite collation.",
            weighting: 2500
        },
        {
            message: "**{character}** s'est callé avec sa petite collation.",
            weighting: 2500
        },
        {
            message: "**{character}** s'est mangé sa petite collation.",
            weighting: 2500
        },
    ],
};
