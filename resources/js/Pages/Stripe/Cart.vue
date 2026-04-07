<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useCart } from '@/cart.js';
import { onMounted, ref } from 'vue';
import axios from 'axios';

const stockErrors = ref([]);

onMounted(async () => {
    const response = await axios.post(route('cart.validate'), { items: items.value });
    if (response.data.errors.length > 0) {
        stockErrors.value = response.data.errors;
    }
});

const { items, total, removeFromCart } = useCart();
</script>

<template>
    <Head title="Your Cart" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shopping Cart</h2>
        </template>
        <template>
            <div v-if="stockErrors.length > 0" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                <p class="font-bold">Inventory Notice:</p>
                <ul class="list-disc ml-5">
                    <li v-for="error in stockErrors" :key="error">{{ error }}</li>
                </ul>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div v-if="items.length > 0">
                        <div v-for="item in items" :key="item.id" class="flex justify-between items-center border-b py-4">
                            <div>
                                <h4 class="font-bold">{{ item.name }}</h4>
                                <p class="text-gray-500">Qty: {{ item.quantity }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-bold">${{ ((item.price * item.quantity) / 100).toFixed(2) }}</span>
                                <button @click="removeFromCart(item.id)" class="text-red-500 hover:underline">Remove</button>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-between items-center">
                            <span class="text-2xl font-bold">Total: ${{ (total / 100).toFixed(2) }}</span>
                            <Link :href="route('checkout.cart')" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-indigo-700">
                                Proceed to Checkout
                            </Link>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-10">
                        <p class="text-gray-500 mb-4">Your cart is empty.</p>
                        <Link :href="route('dashboard')" class="text-indigo-600 hover:underline">Go shopping</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>