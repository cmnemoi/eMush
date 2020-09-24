import {Request, Response} from 'express';
import {Error} from 'sequelize/types';
import {Character} from '../models/character.model';
import CharacterService from '../services/character.service';

export class CharacterController {
    public fetch(req: Request, res: Response) {
        const identifier = req.params.id;

        CharacterService.find(identifier)
            .then((character: Character | null) => {
                if (character === null) {
                    return res.status(404).json();
                }
                return res.json(character);
            })
            .catch((err: Error) => {
                return res.status(500).json(err);
            });
    }

    public fetchAll(req: Request, res: Response) {
        CharacterService.findAll()
            .then((characters: Character[]) => {
                return res.json(characters);
            })
            .catch((err: Error) => {
                return res.status(500).json(err);
            });
    }

    public post(req: Request, res: Response) {
        const name = req.body.name;
        CharacterService.save(Character.build({name}))
            .then((character: Character) => {
                return res.json(character);
            })
            .catch((err: Error) => {
                return res.status(500).json(err);
            });
    }

    public put(req: Request, res: Response) {
        const identifier = req.params.id;
        const name = req.body.name;

        CharacterService.find(identifier)
            .then((character: Character | null) => {
                if (character === null) {
                    return res.status(404).json();
                }
                character.setDataValue('name', name);
                CharacterService.save(character)
                    .then((characterModel: Character) => {
                        return res.json(characterModel);
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
