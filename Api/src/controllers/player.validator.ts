import {body, ValidationChain} from 'express-validator';
import {getAllCharacter} from '../enums/characters.enum';
import DaedalusService from '../services/daedalus.service';
import {Player} from '../models/player.model';
import GameConfig from '../../config/game.config';

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
                        if (daedalus.players.length >= GameConfig.maxPlayer) {
                            throw new Error('Daedalus already full');
                        }
                    }),
                body().custom(async bodyParams => {
                    const daedalus = await DaedalusService.find(
                        bodyParams.daedalus
                    );
                    if (
                        daedalus !== null &&
                        daedalus.players.some(
                            (player: Player) =>
                                player.character === bodyParams.character
                        )
                    ) {
                        throw new Error(
                            'Character already exist on this Daedalus'
                        );
                    }

                    return true;
                }),
            ];
        }
        default:
            return [];
    }
}
