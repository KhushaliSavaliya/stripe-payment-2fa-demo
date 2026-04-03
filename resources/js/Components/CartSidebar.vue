<script setup>
import { useCart } from '@/cart.js';
import { Link } from '@inertiajs/vue3';

const { items, total, removeFromCart, addToCart, updateQuantity } = useCart();
defineProps({ isOpen: Boolean });
defineEmits(['close']);
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50 transition-opacity" @click="$emit('close')"></div>
        
        <div class="fixed inset-y-0 right-0 max-w-full flex">
            <div class="w-screen max-w-md bg-white shadow-xl flex flex-col">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-xl font-bold">Your Cart ({{ items.length }})</h2>
                    <button @click="$emit('close')" class="text-gray-500 hover:text-black text-2xl">✕</button>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    <div v-if="items.length > 0" class="space-y-6">
                        <div v-for="item in items" :key="item.id" class="flex gap-4 border-b pb-4">
                            <img :src="item.image" class="w-16 h-16 rounded object-cover" />
                            <div class="flex-1">
                                <p class="font-bold">{{ item.name }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <button @click="updateQuantity(item.id, -1)" class="bg-gray-200 px-2 rounded">-</button>
                                    <span>{{ item.quantity }}</span>
                                    <button @click="updateQuantity(item.id, 1)" class="bg-gray-200 px-2 rounded">+</button>
                                </div>
                            </div>
                            <p class="font-bold">${{ ((item.price * item.quantity) / 100).toFixed(2) }}</p>
                        </div>
                    </div>
                    <p v-else class="text-center text-gray-500 mt-10">Your cart is empty.</p>
                </div>

                <div class="p-6 border-t bg-gray-50">
                    <div class="flex justify-between text-xl font-bold mb-4">
                        <span>Total:</span>
                        <span>${{ (total / 100).toFixed(2) }}</span>
                    </div>
                    
                    <div class="space-y-3">
                        <Link 
                            :href="route('checkout.cart')" 
                            class="block w-full bg-indigo-600 text-white text-center py-4 rounded-xl font-bold hover:bg-indigo-700 shadow-lg"
                        >
                            Checkout Now
                        </Link>
                        
                        <Link 
                            :href="route('checkout.cart')" 
                            @click="$emit('close')"
                            class="block w-full text-center text-gray-600 text-sm hover:underline"
                        >
                            View Full Cart
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes pulse-cart {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.cart-animate {
    animation: pulse-cart 0.3s ease-in-out;
}
</style>