<script setup>
import { ref, computed, onMounted } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import AppLayout from '@/Layouts/AppLayout.vue';

// استقبال البيانات من السيرفر
const props = defineProps({
  customer: Object,
  transactions: Array
});

// بيانات النموذج
const amount = ref('');
const type = ref('credit'); // credit = له, debit = عليه
const details = ref('');
const errors = ref({});

// دالة لإضافة معاملة جديدة
const addTransaction = () => {
  Inertia.post(`/transactions/store`, {
    customer_id: props.customer.id,
    amount: amount.value,
    type: type.value, // تأكد من إرسال النوع بشكل صحيح
    details: details.value
  }, {
    onSuccess: () => {
      amount.value = '';
      type.value = 'credit'; // إعادة تعيين القيمة الافتراضية إلى "له"
      details.value = '';
      errors.value = {};
    },
    onError: (err) => {
      errors.value = err;
    }
  });
};

// حساب الرصيد النهائي
const finalBalance = computed(() => {
  let balance = 0;
  props.transactions.forEach(transaction => {
    console.log("Transaction:", transaction);
    if (transaction.type === 'credit') {
      balance += parseFloat(transaction.amount); // تأكد من تحويل المبلغ إلى رقم
    } else {
      balance -= parseFloat(transaction.amount); // تأكد من تحويل المبلغ إلى رقم
    }
  });
  console.log("Final Balance:", balance);
  return balance;
});

// تنسيق الرصيد النهائي (إيجابي أخضر - سلبي أحمر)
const balanceColor = computed(() => {
  return finalBalance.value >= 0 ? 'text-green-500' : 'text-red-500';
});

onMounted(() => {
  console.log("تفاصيل العميل:", props.customer);
  console.log("المعاملات:", props.transactions);
});
</script>

<template>
  <AppLayout :title="`تفاصيل العميل: ${customer.name}`">
    <div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
      <h2 class="text-2xl font-semibold mb-4 text-center">تفاصيل العميل: {{ customer.name }}</h2>

      <!-- نموذج إضافة معاملة جديدة -->
      <div class="mb-6 p-4 bg-gray-100 rounded-lg">
        <h3 class="text-lg font-semibold mb-3">إضافة معاملة جديدة</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <input v-model="amount" type="number" placeholder="المبلغ" class="border rounded-lg p-2 w-full" />
          <select v-model="type" class="border rounded-lg p-2 w-full">
            <option value="credit">له</option>
            <option value="debit">عليه</option>
          </select>
          <input v-model="details" type="text" placeholder="الملاحظات" class="border rounded-lg p-2 w-full" />
        </div>
        <button @click="addTransaction" class="mt-3 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
          إضافة المعاملة
        </button>
        <div v-if="errors.amount" class="text-red-500 mt-2">{{ errors.amount }}</div>
      </div>

      <!-- جدول عرض المعاملات -->
      <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 rounded-lg">
          <thead>
            <tr class="bg-gray-100">
              <th class="text-right px-4 py-2 border">التاريخ</th>
              <th class="text-right px-4 py-2 border">المبلغ</th>
              <th class="text-right px-4 py-2 border">الملاحظات</th>
              <th class="text-right px-4 py-2 border">الرصيد (له/عليه)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="transaction in transactions" :key="transaction.id" class="border-b">
              <td class="px-4 py-2 text-right border">{{ transaction.created_at }}</td>
              <td class="px-4 py-2 text-right border">{{ transaction.amount }}</td>
              <td class="px-4 py-2 text-right border">{{ transaction.details }}</td>
              <td class="px-4 py-2 text-right border">
                <span :class="transaction.type === 'credit' ? 'text-green-500' : 'text-red-500'">

                  
                  {{ transaction.type === 'credit' ? 'له' : 'عليه' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- عرض الرصيد النهائي -->
      <div class="mt-4 p-4 text-center border-t">
        <h3 class="text-lg font-semibold">الرصيد النهائي:</h3>
      <p :class="balanceColor" class="text-2xl font-bold">
  {{ finalBalance }} {{ finalBalance >= 0 ? 'له' : 'عليه' }}
</p>
      </div>

    </div>
  </AppLayout>
</template>
