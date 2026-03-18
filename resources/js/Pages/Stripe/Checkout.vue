<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { onMounted, ref } from 'vue';
import { loadStripe } from '@stripe/stripe-js';
import axios from 'axios';

const props = defineProps({
    product: Object,
    stripeKey: String
});

const stripe = ref(null);
const elements = ref(null);
const isLoading = ref(true);
const errorMessage = ref(null);

onMounted(async () => {
    stripe.value = await loadStripe(props.stripeKey);

    try {
        const { data } = await axios.post(route('payment.intent'), {
            product_id: props.product.id
        });

        elements.value = stripe.value.elements({ clientSecret: data.clientSecret });

        const paymentElement = elements.value.create('payment');
        paymentElement.mount('#payment-element');
        
        isLoading.value = false;
    } catch (e) {
        errorMessage.value = "Failed to load payment system.";
    }
});

const handleSubmit = async () => {
    if (!stripe.value || !elements.value) return;

    const { error } = await stripe.value.confirmPayment({
        elements: elements.value,
        confirmParams: {
            return_url: window.location.origin + '/dashboard',
        },
    });

    if (error) {
        errorMessage.value = error.message;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow border">
                <h2 class="text-2xl font-bold mb-6">Complete Payment</h2>
                
                <div class="mb-6 p-4 bg-gray-50 rounded">
                    <p class="font-bold">{{ product.name }}</p>
                    <p class="text-indigo-600 text-xl font-bold">${{ (product.price / 100).toFixed(2) }}</p>
                </div>

                <form @submit.prevent="handleSubmit">
                    <div id="payment-element" class="mb-6"></div>
                    
                    <button 
                        :disabled="isLoading"
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-bold hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ isLoading ? 'Loading...' : 'Pay Securely' }}
                    </button>

                    <div v-if="errorMessage" class="mt-4 text-red-600 text-sm">
                        {{ errorMessage }}
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>