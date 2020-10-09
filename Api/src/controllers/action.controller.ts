import {Request, Response} from 'express';
import {validationResult} from 'express-validator';
import {Action} from '../actions/action';
import PlayerRepository from '../repository/player.repository';
import {createInstance, getActionClass} from '../actions/list.action';
import {Player} from '../models/player.model';
import {logger} from '../config/logger';
import PlayerService from '../services/player.service';
import {User} from '../models/user.model';

export class ActionController {
    public post(req: Request, res: Response): void {
        const errors = validationResult(req); // Finds the validation errors in this request and wraps them in an object with handy functions
        const user = req.user;
        if (!(user instanceof User)) {
            res.status(422).json({errors: 'user not found'});
            return;
        }

        if (!errors.isEmpty()) {
            res.status(422).json({errors: errors.array()});
            return;
        }

        PlayerService.findCurrentPlayer(user).then((player: Player | null) => {
            if (player === null) {
                res.status(422).json({
                    error: 'Player do not have a current game',
                });
                return;
            }

            const actionClassName = getActionClass(req.body.action);

            if (actionClassName === null) {
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
        });
    }
}
