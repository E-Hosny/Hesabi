<script setup>
import { ref, onMounted } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    customers: Array,
    transactions: Array,
    selectedCurrency: String,
});

onMounted(() => {
    console.log("قائمة العملاء:", props.customers);
});

const selectedCurrency = ref(props.selectedCurrency || 'local');

const filterByCurrency = (currency) => {
    selectedCurrency.value = currency;
    Inertia.get('/test', { currency_type: currency }, { preserveState: true });
};

// 🔹 إضافة عميل جديد مع رقم الهاتف (اختياري)
const addCustomer = () => {
    const newName = prompt('أدخل الاسم الجديد:');
    if (!newName) {
        alert('الاسم مطلوب!'); // منع الإدخال الفارغ
        return;
    }

    const newPhone = prompt('أدخل رقم الهاتف (اختياري):');

    Inertia.post('/customers/store', { 
        name: newName, 
        phone: newPhone || null, // إذا كان رقم الهاتف فارغًا يتم إرساله كـ null
        currency_type: selectedCurrency.value 
    }, {
        preserveState: true,
        onSuccess: () => {
            console.log('تمت إضافة العميل بنجاح!');
        },
        onError: (errors) => {
            console.error('حدث خطأ:', errors);
        }
    });
};

// 🔹 تعديل اسم ورقم هاتف العميل
const editCustomer = (id, oldName, oldPhone) => {
    const newName = prompt('أدخل الاسم الجديد:', oldName);
    if (!newName) {
        alert('الاسم مطلوب!');
        return;
    }

    const newPhone = prompt('أدخل رقم الهاتف الجديد:', oldPhone || '');

    Inertia.put(`/customers/${id}`, { name: newName, phone: newPhone || null }, {
        onSuccess: () => {
            console.log('تم تعديل العميل بنجاح!');
        },
        onError: (errors) => {
            console.error('حدث خطأ أثناء التعديل:', errors);
        }
    });
};

// 🔹 حذف العميل
const deleteCustomer = (id) => {
    if (confirm('هل أنت متأكد أنك تريد حذف هذا العميل؟')) {
        Inertia.delete(`/customers/${id}`, {
            preserveState: true,
            onSuccess: () => {
                console.log('تم حذف العميل بنجاح!');
            },
            onError: (errors) => {
                console.error('حدث خطأ أثناء الحذف:', errors);
            }
        });
    }
};
</script>

<template>
  <AppLayout title="Dashboard">
    <div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
      <h2 class="text-xl font-semibold mb-4 text-center">قائمة العملاء</h2>

      <!-- 🔹 خيارات الفلترة بالعملة -->
      <div class="flex justify-center gap-4 my-4">
        <label>
          <input type="radio" value="local" v-model="selectedCurrency" @change="filterByCurrency('local')" />
          محلي
        </label>
        <label>
          <input type="radio" value="usd" v-model="selectedCurrency" @change="filterByCurrency('usd')" />
          دولار
        </label>
        <label>
          <input type="radio" value="sar" v-model="selectedCurrency" @change="filterByCurrency('sar')" />
          ريال
        </label>
      </div>

      <!-- 🔹 عرض الجدول على الشاشات الكبيرة -->
      <div class="hidden sm:block">
        <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-300 rounded-lg">
            <thead>
              <tr class="bg-gray-100">
                <th class="text-right px-4 py-2 border">إجراءات</th>
                <th class="text-right px-4 py-2 border">الرصيد النهائي</th>
                <th class="text-right px-4 py-2 border sm:table-cell">رقم الهاتف</th>
                <th class="text-right px-4 py-2 border">الاسم</th>
                <th class="text-right px-4 py-2 border sm:table-cell">#</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(customer, index) in customers" :key="customer.id" class="border-b">
                <td class="px-4 py-2 text-right border">
                  <div class="flex justify-center gap-2">
                    <button 
                      @click="editCustomer(customer.id, customer.name, customer.phone)" 
                      class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition"
                    >
                      تعديل
                    </button>
                    <button 
                      @click="deleteCustomer(customer.id)" 
                      class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition"
                    >
                      حذف
                    </button>
                  </div>
                </td>

                <td class="px-4 py-2 text-right border">
                  <span :class="customer.final_balance >= 0 ? 'text-green-500' : 'text-red-500'">
                    {{ customer.final_balance }} {{ customer.final_balance >= 0 ? 'له' : 'عليه' }}
                  </span>
                </td>

                <td class="px-4 py-2 text-right border sm:table-cell">
                  {{ customer.phone }}
                </td>

                <td class="px-4 py-2 text-right border">
                  <a :href="`/customers/${customer.id}`" class="text-blue-500 hover:underline">
                    {{ customer.name }}
                  </a>
                </td>

                <td class="px-4 py-2 text-right border sm:table-cell">{{ index + 1 }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 🔹 عرض القائمة كـ "بطاقات" على الجوال -->
      <div class="sm:hidden">
        <div v-for="customer in customers" :key="customer.id" class="p-4 border rounded-lg mb-4 shadow-md">
          <h3 class="text-lg font-semibold text-blue-600">{{ customer.name }}</h3>
          <p class="text-gray-600">📞 {{ customer.phone || "غير متوفر" }}</p>
          <p class="font-semibold mt-2" :class="customer.final_balance >= 0 ? 'text-green-500' : 'text-red-500'">
            {{ customer.final_balance }} {{ customer.final_balance >= 0 ? 'له' : 'عليه' }}
          </p>
          <div class="mt-2 flex justify-end gap-2">
            <button 
              @click="editCustomer(customer.id, customer.name, customer.phone)" 
              class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition"
            >
              تعديل
            </button>
            <button 
              @click="deleteCustomer(customer.id)" 
              class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition"
            >
              حذف
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 🔹 زر إضافة عميل جديد -->
    <div class="mt-4 text-center">
      <button @click="addCustomer" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
        ددددإضافة عميل 
      </button>
    </div>
  </AppLayout>
</template>

