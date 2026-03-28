<script setup>
import { watch, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const message = ref('');

watch(() => page.props.flash.message, (newMessage) => {
    if (newMessage) {
        message.value = newMessage;
        show.value = true;
        setTimeout(() => show.value = false, 5000); // Hide after 5 seconds
    }
}, { immediate: true });
</script>

<template>
    <div v-if="show" class="fixed bottom-5 right-5 bg-green-600 text-white px-6 py-3 rounded-lg shadow-xl z-50 transition-all">
        <div class="flex items-center gap-3">
            <span>✅</span>
            <p class="font-bold">{{ message }}</p>
            <button @click="show = false" class="ml-4 hover:text-gray-200">✕</button>
        </div>
    </div>
</template>