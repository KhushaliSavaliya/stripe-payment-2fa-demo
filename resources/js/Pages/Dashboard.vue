<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3'; // <--- Added Link here

// Define the products prop coming from your ProductController
defineProps({
    products: Array
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Product Catalog
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="product in products" :key="product.id" class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-200">
                        <img :src="product.image" :alt="product.name" class="w-full h-48 object-cover">
                        
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-bold">{{ product.name }}</h3>
                            <p class="text-gray-600 text-sm mt-2 mb-4 line-clamp-2">{{ product.description }}</p>
                            
                            <div class="flex justify-between items-center mt-4">
                                <span class="text-lg font-bold text-indigo-600">
                                    ${{ (product.price / 100).toFixed(2) }}
                                </span>
                                
                                <Link 
                                    :href="route('checkout', product.id)" 
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition"
                                >
                                    Buy Now
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="products.length === 0" class="text-center py-10 text-gray-500">
                    No products found. Did you run the seeder?
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>