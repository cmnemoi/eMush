import { App } from 'vue';
import { Store } from 'vuex';

export function createServiceWorkerListener(store: Store<any>, serviceWorker: ServiceWorkerContainer) {
    return {
        install(app: App) {
            serviceWorker.addEventListener('message', event => {
                if (event.data.type !== 'PUSH_NOTIFICATION') {
                    return;
                }

                store.dispatch('notifications/addNotification', event.data.data.title);
            });
        }
    };
}
