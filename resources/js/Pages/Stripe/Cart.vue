<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useCart } from '@/cart.js';
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';

const stockErrors = ref([]);
const couponCode = ref('');
const discount = ref(0);
const recentViews = ref([]);

onMounted(async () => {
    const response = await axios.post(route('cart.validate'), { items: items.value });
    if (response.data.errors.length > 0) {
        stockErrors.value = response.data.errors;
    }
    recentViews.value = JSON.parse(localStorage.getItem('recent_views') || '[]');
});

const handleCheckout = () => {
    // Send the whole cart to the backend
    router.post(route('checkout.process'), {
        items: items.value,
        total: total.value
    });
};

const applyDiscount = async () => {
    try {
        const response = await axios.post(route('cart.coupon'), { code: couponCode.value });
        discount.value = response.data.discount_percent;
        alert(`Applied ${discount.value}% discount!`);
    } catch (error) {
        alert(error.response.data.message);
    }
};

const discountedTotal = computed(() => {
    const totalCents = total.value;
    return totalCents - (totalCents * (discount.value / 100));
});

const { items, total, removeFromCart } = useCart();

const timeLeft = ref('');
let timerInterval = null;

const startTimer = (expiryDate) => {
    if (timerInterval) clearInterval(timerInterval);
    
    const updateTimer = () => {
        const now = new Date().getTime();
        const distance = new Date(expiryDate).getTime() - now;

        if (distance < 0) {
            clearInterval(timerInterval);
            timeLeft.value = "EXPIRED";
            appliedCoupon.value = null; // Auto-remove coupon if it expires while they watch
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        timeLeft.value = `${hours}h ${minutes}m ${seconds}s`;
    };

    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
};

// Start timer if the applied coupon has an expiry
watch(appliedCoupon, (newVal) => {
    if (newVal?.expires_at) {
        startTimer(newVal.expires_at);
    } else {
        clearInterval(timerInterval);
        timeLeft.value = '';
    }
});

onUnmounted(() => clearInterval(timerInterval));
</script>

<template>
    <Head title="Your Cart" />

    <AuthenticatedLayout>
        <template>
            <div v-if="recentViews.length > 0" class="mt-12 border-t pt-8">
                <h3 class="text-lg font-bold mb-4">Recently Viewed</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-for="product in recentViews" :key="product.id" class="bg-gray-50 p-3 rounded-lg">
                        <p class="font-medium text-sm truncate">{{ product.name }}</p>
                        <button @click="addToCart(product)" class="text-indigo-600 text-xs font-bold hover:underline">
                            + Add Again
                        </button>
                    </div>
                </div>
            </div>
        </template>
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

                        <div class="mt-4 flex gap-2">
                            <input v-model="couponCode" placeholder="Enter Promo Code" class="border rounded px-3 py-2 flex-1" />
                            <button @click="applyDiscount" class="bg-gray-800 text-white px-4 py-2 rounded">Apply</button>
                        </div>

                        <div class="mt-4 text-xl font-bold">
                            <p v-if="discount > 0" class="text-green-600 text-sm">Discount Applied: {{ discount }}%</p>
                        </div>
                        <div v-if="appliedCoupon && appliedCoupon.expires_at" 
                            class="mt-4 p-3 bg-orange-100 border-l-4 border-orange-500 text-orange-700 flex justify-between items-center animate-pulse">
                            <div>
                                <span class="font-bold">Hurry!</span> This offer expires in:
                            </div>
                            <div class="font-mono font-bold text-lg">
                                {{ timeLeft }}
                            </div>
                        </div>


                        
                        <div class="mt-8 flex justify-between items-center">
                            <span>Final Total: ${{ (discountedTotal / 100).toFixed(2) }}</span>
                            <button 
                                @click="handleCheckout" 
                                class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-indigo-700 w-full"
                            >
                                Proceed to Checkout
                            </button>
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