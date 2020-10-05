import {Column, CreateDateColumn, Entity, PrimaryGeneratedColumn, UpdateDateColumn} from "typeorm";

@Entity()
export class User {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column()
    public userId!: string;
    @Column()
    public username!: string;
    public email!: string | null;
    @CreateDateColumn()
    public createdAt!: Date;
    @UpdateDateColumn()
    public updatedAt!: Date;
}
