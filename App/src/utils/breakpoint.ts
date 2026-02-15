import { ref, computed, onMounted, onUnmounted } from 'vue';

/**
 * Return a reference to a boolean indicating if the window width is below the given breakpoint.
 *
 * @param breakpoint The breakpoint to check against.
 *
 * @returns ComputedRef<boolean>
 */
export function useBreakpoint(breakpoint: number) {
    const windowWidth = ref(window.innerWidth);
    const belowBreakpoint = computed(() => windowWidth.value < breakpoint);

    const updateWindowWidth = () => {
        windowWidth.value = window.innerWidth;
    };

    onMounted(() => {
        window.addEventListener('resize', updateWindowWidth);
    });

    onUnmounted(() => {
        window.removeEventListener('resize', updateWindowWidth);
    });

    return belowBreakpoint;
}
