<!-- universal modal -->
<div x-cloak x-show="$store.modal.show" x-trap.noscroll="$store.modal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-bold mb-4" x-text="$store.modal.title"></h3>
        <p class="text-zinc-600 mb-6" x-text="$store.modal.message"></p>
        <input x-cloak x-show="$store.modal.input_bool" type="text" x-model="$store.modal.input" class="w-full border border-zinc-300 rounded-md p-2 mb-6">
        <div class="flex justify-end gap-3">
            <button @click="$store.modal.close()" class="px-4 py-2 text-zinc-600 hover:text-zinc-800" x-text="$store.modal.no"></button>
            <button @click="$store.modal.confirm()" class="px-4 py-2 text-white rounded" :class="$store.modal.yes_class" x-text="$store.modal.yes"></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('modal', {
        show: false,
        title: '',
        message: '',
        callback: null,
        yes: null,
        no: null,
        yes_class: null,
        input_bool: false,
        input: null,
        
        open(title, message, callback, yes, no, yes_class, input_bool = false, input = null) {
            this.title = title;
            this.message = message;
            this.callback = callback;
            this.yes = yes;
            this.no = no;
            this.yes_class = yes_class;
            this.input_bool = input_bool;
            this.input = input;
            this.show = true;
        },

        close() {
            this.show = false;
            this.title = '';
            this.message = '';
            this.callback = null;
            this.yes = null;
            this.no = null;
            this.yes_class = null;
            this.input_bool = false;
            //we do not wipe input because it might be used in the callback afterwards
        },
        
        confirm() {
            let callback = this.callback; //close function will wipe callback
            this.close();
            if (callback) { callback(); }
        }
    });
});
</script>