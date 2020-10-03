import {
    Column,
    CreateDateColumn,
    Entity,
    PrimaryGeneratedColumn,
} from 'typeorm';
import {VisibilityEnum} from '../enums/visibility.enum';
import {LogEnum} from '../enums/log.enum';

@Entity()
export class RoomLog {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column()
    public roomId!: number;
    @Column()
    public playerId!: number;
    @Column()
    public visibility!: VisibilityEnum;
    @Column()
    public message!: LogEnum;
    @CreateDateColumn()
    public createdAt!: Date;
}
