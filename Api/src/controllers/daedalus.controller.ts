import {Request, Response} from 'express';
import {Error} from 'sequelize/types';
import {validationResult} from 'express-validator';
import DaedalusService from '../services/daedalus.service';
import {Daedalus} from '../models/daedalus.model';
import eventManager from "../config/event.manager";

export class DaedalusController {
    public fetch(req: Request, res: Response) {
        const identifier = req.params.id;

        DaedalusService.find(identifier)
            .then((daedalus: Daedalus | null) => {
                if (daedalus === null) {
                    return res.status(404).json();
                }

                DaedalusService.handleCycleChange(daedalus);

                return res.json(daedalus);
            })
            .catch((err: Error) => {
                return res.status(500).json(err);
            });
    }

    public fetchAll(req: Request, res: Response) {
        DaedalusService.findAll()
            .then((daedaluss: Daedalus[]) => {
                return res.json(daedaluss);
            })
            .catch((err: Error) => {
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
                return res.status(500).json(err);
            });
    }

    public put(req: Request, res: Response) {
        const identifier = req.params.id;
        const name = req.body.name;

        DaedalusService.find(identifier)
            .then((daedalus: Daedalus | null) => {
                if (daedalus === null) {
                    return res.status(404).json();
                }
                daedalus.setDataValue('name', name);
                DaedalusService.save(daedalus)
                    .then((daedalusModel: Daedalus) => {
                        return res.json(daedalusModel);
                    })
                    .catch((err: Error) => {
                        return res.status(500).json(err);
                    });
                return;
            })
            .catch((err: Error) => {
                return res.status(500).json(err);
            });
    }

    public patch(req: Request, res: Response) {
        res.status(501).send('Method not implemented!');
    }
    public delete(req: Request, res: Response) {
        res.status(501).send('Method not implemented!');
    }
}
