export default class RandomService {
    /**
     * Generate number [0:nbValuePossible[
     * @param nbValuePossible
     */
    public static random(nbValuePossible = 100): number {
        return Math.floor(Math.random() * nbValuePossible);
    }
}
