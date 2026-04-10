<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    message: String,
    type: { type: String, default: 'success' }
});

const visible = ref(true);

onMounted(() => {
    setTimeout(() => visible.value = false, 3000);
});
</script>

<template>
    <Transition name="fade">
        <div v-if="visible" 
             :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'"
             class="fixed bottom-5 right-5 text-white px-6 py-3 rounded-lg shadow-2xl z-[100] flex items-center gap-3">
            <span>{{ message }}</span>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>