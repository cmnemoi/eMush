import {
    Column,
    CreateDateColumn,
    Entity,
    PrimaryGeneratedColumn,
} from 'typeorm';
import {VisibilityEnum} from '../enums/visibility.enum';

@Entity()
export class RoomLog {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column({name: 'room_id'})
    public roomId!: number;
    @Column({name: 'player_id'})
    public playerId!: number;
    @Column({name: 'visibility'})
    public visibility!: VisibilityEnum;
    @Column({name: 'message'})
    public message!: string;
    @CreateDateColumn({name: 'created_at'})
    public createdAt!: Date;
}
