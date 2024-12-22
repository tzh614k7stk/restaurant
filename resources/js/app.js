window.show_modal = function(message, {
    error = null,
    title = 'Error',
    action = null,
    confirm_text = 'OK',
    cancel_text = 'Cancel',
    yes_class = 'bg-rose-600 hover:bg-rose-700',
    input_bool = false,
    input = null
} = {}) {
    Alpine.store('modal').open(
        title,
        message + (error ? (' ' + (error.response?.data?.message || 'Unknown error.')) : ''),
        action,
        confirm_text,
        cancel_text,
        yes_class,
        input_bool,
        input
    );
};