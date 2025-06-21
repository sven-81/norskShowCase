import {defineStore} from 'pinia';
import {fetchWrapper} from '@/request';
import {router} from '@/router';
import {useAlertStore} from '@/stores';
import {jwtDecode} from "jwt-decode";

const api:string = `${import.meta.env.VITE_BACKEND_URL}`;
export const useAuthStore = defineStore({
    id: 'auth',
    state: () => ({
        // initialize state from local storage to enable user to stay logged in
        user: JSON.parse(localStorage.getItem('user')),
        returnUrl: null
    }),
    actions: {
        async login(username, password) {
            function storeUserDetailsAndJwtInLocalStorageToKeepUserLoggedInBetweenPageRefreshes(user) {
                localStorage.setItem('user', JSON.stringify(user));
            }

            function readScope(user) {
                const token = user.token;
                const decodedToken: any = jwtDecode(token);
                const userScope = decodedToken.scope;
                return {...user, scope: userScope};
            }

            try {
                const user = await fetchWrapper.post(api + `/user`, {username, password});
                const userWithScope = readScope(user);
                this.user = userWithScope;

                storeUserDetailsAndJwtInLocalStorageToKeepUserLoggedInBetweenPageRefreshes(userWithScope);

                // redirect to previous url or default to home page
                await router.push(this.returnUrl || '/');
            } catch (error) {
              const alertStore = useAlertStore();
              alertStore.mapAuthError(error);
            }
        },
        logout() {
            this.user = null;
            localStorage.removeItem('user');
            router.push('/login');
        }
    }
});