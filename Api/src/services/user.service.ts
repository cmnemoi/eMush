import {User} from '../models/user.model';
import UserRepository from '../repository/user.repository';

export default class UserService {
    public static findAll(): Promise<User[]> {
        return UserRepository.findAll();
    }

    public static find(id: number): Promise<User | null> {
        return UserRepository.find(id);
    }

    public static save(user: User): Promise<User> {
        return UserRepository.save(user);
    }
}
