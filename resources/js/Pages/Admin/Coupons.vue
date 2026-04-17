<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({ coupons: Array });
const showModal = ref(false);

const form = useForm({
    code: '',
    discount_percent: '',
});

const deleteCoupon = (id) => {
    if (confirm('Are you sure you want to delete this coupon?')) {
        router.delete(route('coupons.destroy', id));
    }
};

const toggleStatus = (id) => {
    router.patch(route('coupons.toggle', id));
};

const submit = () => {
    form.post(route('coupons.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Manage Coupons" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Active Coupons</h3>
                    <button @click="showModal = true" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        + Create New Coupon
                    </button>
                </div>

                <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded-lg shadow-xl w-96">
                        <h2 class="text-xl font-bold mb-4">New Coupon</h2>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium">Coupon Code</label>
                                <input v-model="form.code" type="text" class="w-full border rounded p-2" placeholder="SUMMER25">
                                <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Discount (%)</label>
                                <input v-model="form.discount_percent" type="number" class="w-full border rounded p-2" placeholder="25">
                                <div v-if="form.errors.discount_percent" class="text-red-500 text-xs mt-1">{{ form.errors.discount_percent }}</div>
                            </div>
                            <div class="flex justify-end gap-2 pt-4">
                                <button type="button" @click="showModal = false" class="text-gray-600 px-4 py-2">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-4 py-2 rounded">
                                    Save Coupon
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                    <h3 class="text-lg font-bold mb-4">Active Coupons</h3>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Code</th>
                                <th class="py-2">Discount</th>
                                <th class="py-2">Usage</th>
                                <th class="py-2">Expires</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="coupon in coupons" :key="coupon.id" class="border-b hover:bg-gray-50">
                                <td class="py-3 font-mono text-indigo-600">{{ coupon.code }}</td>
                                <td class="py-3">{{ coupon.discount_percent }}%</td>
                                <td class="py-3 text-sm">
                                    {{ coupon.used_count }} / {{ coupon.max_uses }}
                                </td>
                                <td class="py-3 text-sm">
                                    {{ coupon.expires_at ? new Date(coupon.expires_at).toLocaleDateString() : 'Never' }}
                                </td>
                                <td class="py-3">
                                    <button 
                                        @click="toggleStatus(coupon.id)"
                                        :class="coupon.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="px-3 py-1 rounded-full text-xs font-semibold hover:opacity-75 transition"
                                    >
                                        {{ coupon.is_active ? 'Active' : 'Inactive' }}
                                    </button>
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