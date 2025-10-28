import { ref } from 'vue';

export function useDoubleTap(callback: () => void, delay = 300) {
    const lastTap = ref(0);

    const handleTap = () => {
        const now = Date.now();
        const timeSinceLastTap = now - lastTap.value;

        if (timeSinceLastTap < delay && timeSinceLastTap > 0) {
            callback();
            lastTap.value = 0;
        } else {
            lastTap.value = now;
        }
    };

    return { handleTap };
}
