import { ref } from 'vue';

const toasts = ref([]);
let nextId = 0;

/**
 * Global toast notification system.
 * Usage: const { showToast } = useToast();
 *        showToast('Saved!', 'success');  // types: success | error | warning | info
 */
export function useToast() {
    const showToast = (message, type = 'info', duration = 3500) => {
        const id = ++nextId;
        toasts.value.push({ id, message, type });
        setTimeout(() => {
            dismiss(id);
        }, duration);
    };

    const dismiss = (id) => {
        const idx = toasts.value.findIndex((t) => t.id === id);
        if (idx !== -1) toasts.value.splice(idx, 1);
    };

    return { toasts, showToast, dismiss };
}
