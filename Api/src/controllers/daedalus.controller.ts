import {Request, Response} from 'express';
import {validationResult} from 'express-validator';
import DaedalusService from '../services/daedalus.service';
import {Daedalus} from '../models/daedalus.model';
import {logger} from '../config/logger';

export class DaedalusController {
    public fetch(req: Request, res: Response): void {
        const identifier = Number(req.params.id);

        DaedalusService.find(identifier)
            .then((daedalus: Daedalus | null) => {
                if (daedalus === null) {
                    res.status(404).json();
                    return;
                }

                DaedalusService.handleCycleChange(daedalus);

                res.json(daedalus);
            })
            .catch((err: Error) => {
                logger.error(err.message);
                res.status(500).json(err);
            });
    }

    public fetchAll(req: Request, res: Response): void {
        DaedalusService.findAll()
            .then((daedaluss: Daedalus[]) => {
                res.json(daedaluss);
            })
            .catch((err: Error) => {
                logger.error(err.message);
                res.status(500).json(err);
            });
    }

    public post(req: Request, res: Response): void {
        const errors = validationResult(req); // Finds the validation errors in this request and wraps them in an object with handy functions

        if (!errors.isEmpty()) {
            res.status(422).json({errors: errors.array()});
            return;
        }
        DaedalusService.initDaedalus()
            .then((daedalus: Daedalus) => {
                res.status(201).json({id : daedalus.id});
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
