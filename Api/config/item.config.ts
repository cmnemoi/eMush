import {ItemsEnum} from '../src/enums/items.enum';
import {ActionsEnum} from '../src/enums/actions.enum';
import {ItemTypeEnum} from '../src/enums/itemType.enum';

export default [
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
];
