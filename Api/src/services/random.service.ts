export default class RandomService {
    public static random(nbValuePossible = 100): number {
        return Math.floor(Math.random() * nbValuePossible);
    }
}
