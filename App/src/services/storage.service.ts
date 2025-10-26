import { User } from "../entities/User";

const USER_INFO = 'user_info';
const OAUTH_STATE = 'oauth_state';

/**
 * Manage how user data is stored.
 *
 * Note: JWT tokens are now stored in httpOnly cookies managed by the backend.
 * This service only handles user info caching in localStorage.
 **/
const TokenService = {
    getUserInfo(): User|null {
        const user = new User();
        const storedUserInfo = localStorage.getItem(USER_INFO);
        if (storedUserInfo !== null) {
            return user.decode(storedUserInfo);
        }
        return null;
    },

    saveUserInfo(user: User): void {
        localStorage.setItem(USER_INFO, user.jsonEncode());
    },

    removeUserInfo(): void {
        localStorage.removeItem(USER_INFO);
    },

    generateOAuthState(): string {
        const array = new Uint8Array(32);
        crypto.getRandomValues(array);
        const state = Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
        sessionStorage.setItem(OAUTH_STATE, state);
        return state;
    },

    validateOAuthState(state: string): boolean {
        const storedState = sessionStorage.getItem(OAUTH_STATE);
        sessionStorage.removeItem(OAUTH_STATE); // Consume the state (single use)
        return storedState !== null && storedState === state;
    }

};

export { TokenService };
