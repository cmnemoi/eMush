import {User} from '../models/user.model';
import database from '../config/database';

export default class UserRepository {
    public static findAll(): Promise<User[]> {
        return database.then(async connection => {
            const userRepository = connection.getRepository(User);
            return userRepository.find();
        });
    }

    public static find(id: number): Promise<User | null> {
        return database.then(async connection => {
            const userRepository = connection.getRepository(User);
            return userRepository
                .findOne(id, {
                    relations: ['games'],
                })
                .then((result: User | undefined) => {
                    return typeof result === 'undefined' ? null : result;
                });
        });
    }

    public static findByUserId(userId: string): Promise<User | null> {
        return database.then(async connection => {
            const userRepository = connection.getRepository(User);
            return userRepository
                .findOne({where: {userId}})
                .then((result: User | undefined) => {
                    return typeof result === 'undefined' ? null : result;
                });
        });
    }

    public static save(user: User): Promise<User> {
        return database.then(async connection => {
            const userRepository = connection.getRepository(User);
            return userRepository.save(user);
        });
    }
}
