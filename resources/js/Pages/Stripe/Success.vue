<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { onMounted } from 'vue';
import { useCart } from '@/cart.js';

const { clearCart } = useCart();
const props = defineProps({ order: Object, amount: Number });

onMounted(() => {
    // Important: Only clear the local cart once the DB confirms payment
    clearCart();
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12 text-center">
            <div class="max-w-2xl mx-auto bg-white p-10 rounded-xl shadow-lg border-t-4 border-green-500">
                
                <div class="flex justify-center mb-4">
                    <div class="rounded-full bg-green-100 p-3">
                        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <h1 class="text-3xl font-bold text-gray-900">Payment Successful!</h1>
                <p class="text-gray-600 mt-2 text-lg">Order #{{ order.id }} has been processed.</p>
                
                <div class="mt-8 border-t pt-6 text-left">
                    <h3 class="font-bold text-gray-800 mb-4">Order Summary:</h3>
                    
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-700">
                            {{ item.product?.name || 'Product' }} 
                            <span class="text-gray-400 text-sm">x{{ item.quantity }}</span>
                        </span>
                        <span class="font-medium">${{ (item.price_at_purchase / 100).toFixed(2) }}</span>
                    </div>

                    <div class="flex justify-between font-bold text-xl mt-6 text-indigo-600 bg-indigo-50 p-4 rounded-lg">
                        <span>Total Paid:</span>
                        <span>${{ amount.toFixed(2) }}</span>
                    </div>
                </div>

                <a :href="route('dashboard')" class="mt-10 inline-block bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-700 transition shadow-md">
                    Back to Shop
                </a>
            </div>
        </div>
    </AuthenticatedLayout>
</template>