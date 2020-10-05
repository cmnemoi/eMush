import {ItemsEnum} from '../src/enums/items.enum';
import {ActionsEnum} from '../src/enums/actions.enum';
import {ItemTypeEnum} from '../src/enums/itemType.enum';

export interface FoodEffects {
    actionPoint: number,
    movementPoint: number,
    healthPoint: number,
    moralPoint: number,
    satiety: number,
}
export interface ItemConfig {
    name: ItemsEnum,
    type: ItemTypeEnum,
    actions: ActionsEnum[],
    effects?: FoodEffects | undefined,
    isHeavy: boolean,
    isDismantable: boolean,
    isStackable: boolean,
    isHideable: boolean,
    isMoveable: boolean,
    isFireDestroyable: boolean,
    isFireBreakable: boolean,
}

const itemsConfig : ItemConfig[]  = [
    {
        name: ItemsEnum.STANDARD_RATION,
        type: ItemTypeEnum.RATION,
        actions: [ActionsEnum.EAT],
        effects: {
            actionPoint: 4,
            movementPoint: 0,
            healthPoint: 0,
            moralPoint: -1,
            satiety: 4,
        },
        isHeavy: false,
        isDismantable: false,
        isStackable: true,
        isHideable: true,
        isMoveable: true,
        isFireDestroyable: true,
        isFireBreakable: false,
    },
    {
        name: ItemsEnum.STAINPROOF_APRON,
        type: ItemTypeEnum.GEAR,
        actions: [],
        isHeavy: false,
        isDismantable: false,
        isStackable: false,
        isHideable: true,
        isMoveable: true,
        isFireDestroyable: false,
        isFireBreakable: true,
    },
];

export default itemsConfig;
