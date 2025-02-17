<script setup>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import {onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';




const props = defineProps({
    customers: Array,
});

onMounted(()=>{
    console.log("قائمة العملاء:", props.customers);

});



const names = ref([
    { id: 1, name: 'محمد' },
    { id: 2, name: 'أحمد' },
    { id: 3, name: 'خالد' },
]);

const addName = () => {
    const newName = prompt('أدخل الاسم الجديد:');
    if (newName) {
            Inertia.post('/customers/store', { name: newName }, {
            onSuccess: () => {
                console.log('تمت إضافة الاسم بنجاح!');
            },
            onError: (errors) => {
                console.error('حدث خطأ:', errors);
            }
        });    }
};

const editName = (id, oldName) => {
    const newName = prompt('أدخل الاسم الجديد:', oldName);
    if (newName && newName !== oldName) {
        Inertia.put(`/customers/${id}`, { name: newName }, {
            onSuccess: () => {
                console.log('تم تعديل الاسم بنجاح!');
            },
            onError: (errors) => {
                console.error('حدث خطأ أثناء التعديل:', errors);
            }
        });
    }
};



</script>

<template>

  <AppLayout title="Dashboard">

  <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
        <h2 class="text-xl font-semibold mb-4 text-center">قائمة العملاء</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 rounded-lg">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-right px-4 py-2 border">#</th>
                        <th class="text-right px-4 py-2 border">الاسم</th>
                        <th class="text-right px-4 py-2 border">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(customer, index) in props.customers" :key="customer.id" class="border-b">
                        <td class="px-4 py-2 text-right border">{{ index + 1 }}</td>
                        <td class="px-4 py-2 text-right border">
                            <a :href="`/customers/${customer.id}`" class="text-blue-500 hover:underline">
                              {{ customer.name }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-right border">
                            <button @click="editName(customer.id, customer.name)" class="text-blue-500 hover:underline ml-2">تعديل</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4 text-center">
        <button @click="addName" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">إضافة اسم جديد</button>
    </div>
  </AppLayout>

</template>
