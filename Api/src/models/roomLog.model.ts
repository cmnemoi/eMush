import {Column, CreateDateColumn, Entity, PrimaryGeneratedColumn, UpdateDateColumn} from "typeorm";

@Entity()
export class RoomLog {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column()
    public roomId!: number;
    @Column()
    public log!: string;
    @CreateDateColumn()
    public createdAt!: Date;
}

