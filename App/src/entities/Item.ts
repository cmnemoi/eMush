import { Equipment } from "@/entities/Equipment";

export interface Item extends Equipment {
    number: number
    effectTitle: string
    effects: string[]
}
