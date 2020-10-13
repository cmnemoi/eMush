import {Request, Response} from 'express';
import {validationResult} from 'express-validator';
import DaedalusService from '../services/daedalus.service';
import {Daedalus} from '../models/daedalus.model';
import {logger} from '../config/logger';
import {daedalusSerializer} from "../serializer/daedalus.serializer";
import {User} from "../models/user.model";

export class DaedalusController {
    public fetch(req: Request, res: Response): void {
        const user = req.user;
        const identifier = Number(req.params.id);

        if (!(user instanceof User)) {
            res.status(403).json({errors: 'user not found'});
            return;
        }

        DaedalusService.find(identifier)
            .then((daedalus: Daedalus | null) => {
                if (daedalus === null) {
                    res.status(404).json();
                    return;
                }

                DaedalusService.handleCycleChange(daedalus);

                res.json(daedalusSerializer(daedalus, user));
            })
            .catch((err: Error) => {
                logger.error(err.message);
                res.status(500).json(err);
            });
    }

    public fetchAll(req: Request, res: Response): void {
        const user = req.user;

        if (!(user instanceof User)) {
            res.status(403).json({errors: 'user not found'});
            return;
        }
        DaedalusService.findAll()
            .then((daedaluss: Daedalus[]) => {
                res.json(
                    daedaluss.map((daedalus: Daedalus) =>
                        daedalusSerializer(daedalus, user)
                    )
                );
            })
            .catch((err: Error) => {
                logger.error(err.message);
                res.status(500).json(err);
            });
    }

    public post(req: Request, res: Response): void {
        const errors = validationResult(req); // Finds the validation errors in this request and wraps them in an object with handy functions
        const user = req.user;

        if (!(user instanceof User)) {
            res.status(403).json({errors: 'user not found'});
            return;
        }

        if (!errors.isEmpty()) {
            res.status(422).json({errors: errors.array()});
            return;
        }
        DaedalusService.initDaedalus()
            .then((daedalus: Daedalus) => {
                res.json(daedalusSerializer(daedalus, user));
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
