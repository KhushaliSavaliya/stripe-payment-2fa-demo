<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
    stripeKey: String
});
</script>

<template>
    <Head title="Secure Checkout" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col md:flex-row gap-8">
                    
                    <div class="w-full md:w-1/2">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                        <div class="flex items-center gap-4 p-4 border rounded-lg">
                            <img :src="product.image" class="w-20 h-20 object-cover rounded shadow">
                            <div>
                                <h4 class="font-bold">{{ product.name }}</h4>
                                <p class="text-indigo-600 font-bold">${{ (product.price / 100).toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="w-full md:w-1/2 border-t md:border-t-0 md:border-l pt-6 md:pt-0 md:pl-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Details</h3>
                        <div class="bg-gray-50 p-10 rounded-lg border-2 border-dashed border-gray-200 text-center">
                            <p class="text-gray-500 text-sm">Stripe Payment Element (2FA/3DS) will be injected here in the next step.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>