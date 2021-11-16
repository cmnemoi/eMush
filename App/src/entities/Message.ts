import { Character } from "@/entities/Character";

export interface Message {
    id : number|null
    message : string|null
    character : Character
    child : Array<Message>
    date : Date|null
}
