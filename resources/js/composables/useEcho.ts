import { ref } from 'vue';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const echoInstance = ref<Echo | null>(null);
const isInitialized = ref(false);

window.Pusher = Pusher;

export function useEcho() {
    const initializeEcho = () => {
        if (isInitialized.value || echoInstance.value) {
            return echoInstance.value;
        }

        window.Pusher = Pusher;

        echoInstance.value = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            forceTLS: true,
        });

        window.Echo = echoInstance.value;
        isInitialized.value = true;

        return echoInstance;
    };

    const disconnect = () => {
        if (echoInstance.value) {
            echoInstance.value.disconnect();
            echoInstance.value = null;
            window.Echo = undefined;
            isInitialized.value = false;
        }
    };

    return {
        initializeEcho,
        disconnect,
        isInitialized,
    };
}