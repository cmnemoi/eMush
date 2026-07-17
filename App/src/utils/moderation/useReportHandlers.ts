import { ref } from 'vue';

type ReportSubmitHandler = (params: URLSearchParams) => Promise<void> | void;

/**
 * Shared state/behavior for a ReportPopup instance.
 */
export function useReportHandlers() {
    const visible = ref(false);
    let submitHandler: ReportSubmitHandler | null = null;

    const open = (handler: ReportSubmitHandler): void => {
        submitHandler = handler;
        visible.value = true;
    };

    const close = (): void => {
        visible.value = false;
    };

    const submit = async (params: URLSearchParams): Promise<void> => {
        await submitHandler?.(params);
        visible.value = false;
    };

    return { visible, open, close, submit };
}
