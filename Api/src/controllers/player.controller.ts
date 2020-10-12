import {Request, Response} from 'express';
import {Player} from '../models/player.model';
import PlayerService from '../services/player.service';
import DaedalusService from '../services/daedalus.service';
import {Daedalus} from '../models/daedalus.model';
import {validationResult} from 'express-validator';
import {logger} from '../config/logger';
import {User} from '../models/user.model';
import {playerSerializer} from "../serializer/player.serializer";

export class PlayerController {
    public fetch(req: Request, res: Response): void {
        const user = req.user;
        const identifier = Number(req.params.id);

        if (!(user instanceof User)) {
            res.status(422).json({errors: 'user not found'});
            return;
        }

        PlayerService.find(identifier)
            .then((player: Player | null) => {
                if (player instanceof Player) {
                    try {
                        res.json(playerSerializer(player, user));
                    } catch (err) {
                        console.log(err)
                    }
                    return;
                }

                res.status(404).json();
            })
            .catch((err: Error) => {
                res.status(500).json(err);
            });
    }

    public fetchAll(req: Request, res: Response): void {
        PlayerService.findAll()
            .then((players: Player[]) => {
                res.json(players);
            })
            .catch((err: Error) => {
                res.status(500).json(err);
            });
    }

    public post(req: Request, res: Response): void {
        const user = req.user;
        const character = req.body.character;

        if (!(user instanceof User)) {
            res.status(422).json({errors: 'user not found'});
            return;
        }

        const errors = validationResult(req); // Finds the validation errors in this request and wraps them in an object with handy functions

        if (!errors.isEmpty()) {
            res.status(422).json({errors: errors.array()});
            return;
        }

        PlayerService.findCurrentPlayer(user)
            .then((userPlayer: Player | null) => {
                if (userPlayer !== null) {
                    // @FIXME:  do that in validation
                    res.status(422).json('User is already in a game');
                    return;
                }
                DaedalusService.find(req.body.daedalus)
                    .then((daedalus: Daedalus | null) => {
                        if (daedalus === null) {
                            // @FIXME:  do that in validation
                            res.status(422).json(
                                'Invalid Daedalus identifier provided : ' +
                                    req.body.daedalus
                            );
                            return;
                        }

                        PlayerService.initPlayer(user, daedalus, character)
                            .then((player: Player) => {
                                res.status(201).json(player);
                            })
                            .catch((err: Error) => {
                                logger.error(err.message);
                                res.status(500).json(err);
                            });
                    })
                    .catch((err: Error) => {
                        logger.error(err.message);
                        res.status(500).json(err);
                    });
            })
            .catch((err: Error) => {
                logger.error(err.message);
                res.status(500).json(err);
            });
    }

    public put(req: Request, res: Response): void {
        res.status(501).send('Method not implemented!');
    }

    public patch(req: Request, res: Response): void {
        res.status(501).send('Method not implemented!');
    }
    public delete(req: Request, res: Response): void {
        res.status(501).send('Method not implemented!');
    }
}
