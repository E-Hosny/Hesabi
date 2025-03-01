<script setup>
import { ref, computed, onMounted } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  customer: Object,
  transactions: Array
});

const amount = ref('');
const type = ref('credit'); 
const details = ref('');
const errors = ref({});
const editingTransaction = ref(null); // تخزين المعاملة التي يتم تعديلها
const editedAmount = ref('');
const editedType = ref('');
const editedDetails = ref('');
const searchQuery = ref('');

// 🔍 تصفية المعاملات بناءً على البحث
const filteredTransactions = computed(() => {
  if (!searchQuery.value) return props.transactions;
  return props.transactions.filter(transaction =>
    transaction.details?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    transaction.amount.toString().includes(searchQuery.value) ||
    (transaction.type === 'credit' ? 'له' : 'عليه').includes(searchQuery.value)
  );
});



// إضافة معاملة جديدة
const addTransaction = () => {
  Inertia.post(`/transactions/store`, {
    customer_id: props.customer.id,
    amount: amount.value,
    type: type.value, 
    details: details.value
  }, {
    onSuccess: () => {
      amount.value = '';
      type.value = 'credit'; 
      details.value = '';
      errors.value = {};
    },
    onError: (err) => {
      errors.value = err;
    }
  });
};

// بدء التعديل على المعاملة
const startEditing = (transaction) => {
  editingTransaction.value = transaction;
  editedAmount.value = transaction.amount;
  editedType.value = transaction.type;
  editedDetails.value = transaction.details;
};

// تحديث المعاملة
const updateTransaction = () => {
  if (!editingTransaction.value) return;

  Inertia.put(`/transactions/${editingTransaction.value.id}`, {
    amount: editedAmount.value,
    type: editedType.value,
    details: editedDetails.value
  }, {
    onSuccess: () => {
      editingTransaction.value = null;  // إغلاق نموذج التعديل بعد النجاح
    },
    onError: (err) => {
      errors.value = err;
    }
  });
};

const deleteTransaction = (id) => {
  if (confirm('هل أنت متأكد أنك تريد حذف هذه المعاملة؟')) {
    Inertia.delete(`/transactions/${id}`, {
      preserveState: true,
      onSuccess: () => {
        console.log('تم حذف المعاملة بنجاح!');
      },
      onError: (errors) => {
        console.error('حدث خطأ أثناء الحذف:', errors);
      }
    });
  }
};


// حساب الرصيد النهائي
const finalBalance = computed(() => {
  let balance = 0;
  props.transactions.forEach(transaction => {
    if (transaction.type === 'credit') {
      balance += parseFloat(transaction.amount); 
    } else {
      balance -= parseFloat(transaction.amount); 
    }
  });
  return balance;
});

// تحديد لون الرصيد
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

      <div class="mb-6 p-4 bg-gray-100 rounded-lg flex items-center justify-between">
        <h3 class="text-lg font-semibold">سجل المعاملات</h3>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="🔍 ابحث .."
          class="border rounded-lg p-2 w-1/3 focus:ring focus:border-blue-300 transition"
        />
      </div>

     <div class="mb-6 p-4 bg-gray-100 rounded-lg">
  <h3 class="text-lg font-semibold mb-3">إضافة معاملة جديدة</h3>
  
  <div class="flex flex-wrap gap-2">
    <input v-model="amount" type="number" placeholder="المبلغ" 
           class="border rounded-lg p-2 flex-1 min-w-[30%]" />
    
    <select v-model="type" class="border rounded-lg p-2 flex-1 min-w-[30%]">
      <option value="credit">له</option>
      <option value="debit">عليه</option>
    </select>
    
    <input v-model="details" type="text" placeholder="الملاحظات" 
           class="border rounded-lg p-2 flex-1 min-w-[30%]" />
  </div>

  <button @click="addTransaction" 
          class="mt-3 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
    إضافة المعاملة
  </button>

  <div v-if="errors.amount" class="text-red-500 mt-2">{{ errors.amount }}</div>
</div>


      <!-- حاوية قابلة للتمرير أفقيًا -->
<div class="overflow-x-auto">
  <table class="table-auto w-full border border-gray-300 rounded-lg text-sm">
    <thead>
      <tr class="bg-gray-100">
        <th class="text-right px-2 py-1 border">الإجراءات</th>
        <th class="text-right px-2 py-1 border">الرصيد المتبقي</th>
        <th class="text-right px-2 py-1 border">الملاحظات</th>
        <th class="text-right px-2 py-1 border">المبلغ</th>
        <th class="text-right px-2 py-1 border">التاريخ</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="transaction in filteredTransactions" :key="transaction.id" class="border-b">
        <td class="px-2 py-1 text-right border">
          <div class="flex justify-center gap-1">
            <button @click="startEditing(transaction)" class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs">
              ✏️
            </button>
            <button @click="deleteTransaction(transaction.id)" class="bg-red-500 text-white px-2 py-1 rounded-md text-xs">
              🗑️
            </button>
          </div>
        </td>
        <td class="px-2 py-1 text-right border font-bold" 
            :class="transaction.remaining_balance >= 0 ? 'text-green-500' : 'text-red-500'">
          {{ transaction.remaining_balance }}
        </td>
        <td class="px-2 py-1 text-right border break-words whitespace-normal max-w-[100px] sm:max-w-[150px] md:max-w-[200px] lg:max-w-xs">
          {{ transaction.details }}
        </td>
        <td class="px-2 py-1 text-right border">{{ transaction.type === 'credit' ? '+' : '-' }}{{ transaction.amount }}</td>
        <td class="px-2 py-1 text-right border">{{ transaction.created_at }}</td>
      </tr>
    </tbody>
  </table>
</div>

      <div v-if="editingTransaction" class="p-4 bg-gray-100 rounded-lg mt-4">
        <h3 class="text-lg font-semibold mb-3">تعديل المعاملة</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <input v-model="editedAmount" type="number" placeholder="المبلغ" class="border rounded-lg p-2 w-full" />
          <select v-model="editedType" class="border rounded-lg p-2 w-full">
            <option value="credit">له</option>
            <option value="debit">عليه</option>
          </select>
          <input v-model="editedDetails" type="text" placeholder="الملاحظات" class="border rounded-lg p-2 w-full" />
        </div>
        <div class="mt-3 flex gap-2">
          <button @click="updateTransaction" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
            حفظ التعديلات
          </button>
          <button @click="editingTransaction= null" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            إلغاء
          </button>
        </div>
      </div>

      <div class="mt-4 p-4 text-center border-t">
        <h3 class="text-lg font-semibold">الرصيد النهائي:</h3>
        <p :class="balanceColor" class="text-2xl font-bold">
          {{ finalBalance }} {{ finalBalance >= 0 ? 'له' : 'عليه' }}
        </p>
      </div>
    </div>
  </AppLayout>
</template>
