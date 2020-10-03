import {Request, Response} from 'express';
import {validationResult} from 'express-validator';
import {Action} from '../actions/action';
import PlayerRepository from '../repository/player.repository';
import {createInstance, getActionClass} from '../actions/list.action';
import {Player} from '../models/player.model';
import {logger} from '../config/logger';

export class ActionController {
    public post(req: Request, res: Response): void {
        const errors = validationResult(req); // Finds the validation errors in this request and wraps them in an object with handy functions

        if (!errors.isEmpty()) {
            res.status(422).json({errors: errors.array()});
            return;
        }
        PlayerRepository.find(Number(req.body.player)).then(
            (player: Player | null) => {
                const actionClassName = getActionClass(req.body.action);

                if (typeof actionClassName === 'undefined') {
                    res.status(422).json({
                        error: 'invalid action provided',
                    });
                    return;
                }

                const action: Action = createInstance(actionClassName, player);

                action.loadParams(req.body.params).then((isLoaded: boolean) => {
                    if (!isLoaded) {
                        res.status(422).json({
                            error: 'invalid action parameter provided',
                        });
                        return;
                    }

                    action
                        .execute()
                        .then(result => {
                            return res.status(200).json(result);
                        })
                        .catch((err: Error) => {
                            logger.error(err.message);
                            res.status(500).json(err);
                        });
                });
            }
        );
    }
}
