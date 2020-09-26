import {body, ValidationChain} from 'express-validator';
import {getAllCharacter} from '../enums/characters.enum';
import DaedalusService from '../services/daedalus.service';

export const POST_PLAYER = 'postPlayer';

export function validate(method: string): ValidationChain[] {
    switch (method) {
        case POST_PLAYER: {
            return [
                body('character', 'character is required')
                    .exists()
                    .isIn(getAllCharacter()),
                body('daedalus', 'daedalus is required')
                    .exists()
                    .custom(async daedalusId => {
                        const daedalus = await DaedalusService.find(daedalusId);
                        if (daedalus === null) {
                            throw new Error('Daedalus does not exist');
                        }
                    }),
            ];
        }
        default:
            return [];
    }
}
