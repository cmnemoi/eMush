import {Request, Response} from 'express';
import {validationResult} from 'express-validator';
import DaedalusService from '../services/daedalus.service';
import {Daedalus} from '../models/daedalus.model';
import {logger} from '../config/logger';

export class DaedalusController {
    public fetch(req: Request, res: Response) {
        const identifier = Number(req.params.id);

        DaedalusService.find(identifier)
            .then((daedalus: Daedalus | null) => {
                if (daedalus === null) {
                    return res.status(404).json();
                }

                DaedalusService.handleCycleChange(daedalus);

                return res.json(daedalus);
            })
            .catch((err: Error) => {
                logger.error(err.message);
                return res.status(500).json(err);
            });
    }

    public fetchAll(req: Request, res: Response) {
        DaedalusService.findAll()
            .then((daedaluss: Daedalus[]) => {
                return res.json(daedaluss);
            })
            .catch((err: Error) => {
                logger.error(err.message);
                return res.status(500).json(err);
            });
    }

    public post(req: Request, res: Response) {
        const errors = validationResult(req); // Finds the validation errors in this request and wraps them in an object with handy functions

        if (!errors.isEmpty()) {
            res.status(422).json({errors: errors.array()});
            return;
        }
        DaedalusService.initDaedalus()
            .then((daedalus: Daedalus) => {
                return res.status(201).json(daedalus);
            })
            .catch((err: Error) => {
                logger.error(err.message);
                return res.status(500).json(err);
            });
    }

    public put(req: Request, res: Response) {
        res.status(501).send('Method not implemented!');
    }

    public patch(req: Request, res: Response) {
        res.status(501).send('Method not implemented!');
    }
    public delete(req: Request, res: Response) {
        res.status(501).send('Method not implemented!');
    }
}
