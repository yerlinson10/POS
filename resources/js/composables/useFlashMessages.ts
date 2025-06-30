import { toast } from 'vue-sonner'
import 'vue-sonner/style.css'
import {  computed, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

export enum MessageType {
    error = 'error',
    success = 'success'
}

export interface FlashMessage {
    type: MessageType;
    text: string;
}

export default function useFlashMessage() {
    const page = usePage();
    const flash = computed(() => (page.props.flash as { message?: FlashMessage })?.message);

    onMounted(() => {
        if (flash.value) {
            displayFlashMessage(flash.value)
        }
    });

    watch(
        () => flash.value,
        (newFlash, oldFlash) => {
            // Solo mostrar si cambia y no es igual al anterior
            if (newFlash?.text && newFlash?.text !== oldFlash?.text) {
                displayFlashMessage(newFlash)
            }
        }
    );

    function displayFlashMessage(message: FlashMessage): void {
        toast[message.type](message.text ?? '');
    }
}


