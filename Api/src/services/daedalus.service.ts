import {Daedalus} from '../models/daedalus.model';
import {Identifier} from 'sequelize';

export default class DaedalusService {
    public static findAll(): Promise<Daedalus[]> {
        return Daedalus.findAll<Daedalus>({});
    }

    public static find(id: Identifier): Promise<Daedalus | null> {
        return Daedalus.findByPk<Daedalus>(id);
    }

    public static save(daedalus: Daedalus): Promise<Daedalus> {
        return daedalus.save();
    }
}
