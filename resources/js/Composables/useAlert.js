import { ref, reactive } from 'vue';

const state = reactive({
    show: false,
    title: '',
    message: '',
    type: 'alert', // 'alert' or 'confirm'
    resolve: null,
    variant: 'info' // 'info', 'warning', 'error', 'success'
});

export function useAlert() {
    const showAlert = (message, title = 'Attention', variant = 'info') => {
        state.message = message;
        state.title = title;
        state.type = 'alert';
        state.variant = variant;
        state.show = true;
    };

    const showConfirm = (message, title = 'Are you sure?', variant = 'warning') => {
        state.message = message;
        state.title = title;
        state.type = 'confirm';
        state.variant = variant;
        state.show = true;

        return new Promise((resolve) => {
            state.resolve = resolve;
        });
    };

    const close = (result = false) => {
        state.show = false;
        if (state.resolve) {
            state.resolve(result);
            state.resolve = null;
        }
    };

    return {
        state,
        showAlert,
        showConfirm,
        close
    };
}
