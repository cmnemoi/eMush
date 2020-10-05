export default class RandomService {

    /**
     * Generate number [0:nbValuePossible[
     * @param nbValuePossible
     */
    public static random(nbValuePossible: number = 100): number {
        return Math.floor(Math.random() * nbValuePossible);
    }
}
