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
                <div class="text-green-500 text-6xl mb-4">Checkmark Icon</div>
                <h1 class="text-3xl font-bold text-gray-900">Payment Successful!</h1>
                <p class="text-gray-600 mt-2 text-lg">Order #{{ order.id }} has been processed.</p>
                
                <div class="mt-8 border-t pt-6 text-left">
                    <h3 class="font-bold text-gray-800">Order Summary:</h3>
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between py-2 border-b border-gray-50">
                        <span>{{ item.product.name }} (x{{ item.quantity }})</span>
                        <span>${{ (item.price_at_purchase / 100).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-xl mt-4 text-indigo-600">
                        <span>Total Paid:</span>
                        <span>${{ amount.toFixed(2) }}</span>
                    </div>
                </div>

                <a :href="route('dashboard')" class="mt-10 inline-block bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-700">
                    Back to Shop
                </a>
            </div>
        </div>
    </AuthenticatedLayout>
</template>