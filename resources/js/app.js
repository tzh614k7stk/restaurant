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

window.parse_duration = function(duration) {
    let result = '';
    let hours = Math.floor(duration);
    let minutes = Math.floor((duration - hours) * 60);
    if (hours > 0) { result += hours + ' hour' + (hours > 1 ? 's' : ''); }
    if (minutes > 0) { result += ' ' + minutes + ' minutes'; }
    return result;
};

window.style_date = function(dt) { return dt.getDate().toString().padStart(2, '0') + '-' + (dt.getMonth() + 1).toString().padStart(2, '0') + '-' + dt.getFullYear(); };
window.style_time = function(dt) { return dt.getHours().toString().padStart(2, '0') + ':' + dt.getMinutes().toString().padStart(2, '0'); };
window.style_html_date = function(dt) { return dt.getFullYear() + '-' + (dt.getMonth() + 1).toString().padStart(2, '0') + '-' + dt.getDate().toString().padStart(2, '0'); };
window.style_human_date = function(dt) { return dt.toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'}); };