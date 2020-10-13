import {Request, Response} from 'express';
import {User} from '../models/user.model';
import UserRepository from '../repository/user.repository';
import {userSerializer} from '../serializer/user.serializer';

export class UserController {
    public fetch(req: Request, res: Response): void {
        const user = req.user;
        const identifier = Number(req.params.id);

        if (!(user instanceof User)) {
            res.status(403).json({errors: 'user not found'});
            return;
        }

        if (user.id !== identifier) {
            res.status(403).json({errors: 'Not allowed'});
            return;
        }

        UserRepository.find(identifier)
            .then(async (userModel: User | null) => {
                if (userModel instanceof User) {
                    res.json(await userSerializer(userModel, user));
                    return;
                }

                res.status(404).json();
            })
            .catch((err: Error) => {
                res.status(500).json(err);
            });
    }

    public async me(req: Request, res: Response): Promise<void> {
        const user = req.user;
        if (!(user instanceof User)) {
            res.status(403).json({errors: 'user not found'});
            return;
        }

        res.json(await userSerializer(user, user));
    }
}
