import {Character} from "../models/character.model";
import {Identifier} from "sequelize";

export default class CharacterService {

    public static findAll(): Promise<Character[]>
    {
        return Character.findAll<Character>({});
    }

    public static find(name : Identifier): Promise<Character>
    {
        return Character.findByPk<Character>(name);
    }

    public static save(character : Character): Promise<Character>
    {
        return character.save();
    }
}