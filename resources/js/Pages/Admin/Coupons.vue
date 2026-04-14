<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({ coupons: Array });

const deleteCoupon = (id) => {
    if (confirm('Are you sure you want to delete this coupon?')) {
        router.delete(route('coupons.destroy', id));
    }
};
</script>

<template>
    <Head title="Manage Coupons" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4">Active Coupons</h3>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Code</th>
                                <th class="py-2">Discount</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="coupon in coupons" :key="coupon.id" class="border-b hover:bg-gray-50">
                                <td class="py-3 font-mono text-indigo-600">{{ coupon.code }}</td>
                                <td class="py-3">{{ coupon.discount_percent }}%</td>
                                <td class="py-3">
                                    <span :class="coupon.is_active ? 'text-green-600' : 'text-red-600'">
                                        {{ coupon.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <button @click="deleteCoupon(coupon.id)" class="text-red-500 hover:underline">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>