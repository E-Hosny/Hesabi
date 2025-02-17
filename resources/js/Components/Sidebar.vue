<template>
  <!-- Sidebar Overlay (Only on mobile) -->
  <div :class="sidebarClass" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 lg:hidden" @click="toggleSidebar"></div>

  <!-- Sidebar -->
  <div :class="sidebarClass" class="lg:w-64 w-1/2 bg-white shadow-md z-40 fixed h-full">
    <div class="p-4">
      <h2 class="text-lg font-semibold">قائمة التنقل</h2>
      <ul class="mt-4">
        <li><a href="#" class="text-gray-700 hover:text-indigo-600">الرئيسية</a></li>
        <li><a href="#" class="text-gray-700 hover:text-indigo-600">الملف الشخصي</a></li>
        <li><a href="#" class="text-gray-700 hover:text-indigo-600">الإعدادات</a></li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const sidebarOpen = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const sidebarClass = computed(() => {
  return sidebarOpen.value ? 'block' : 'hidden';
});
</script>

<style scoped>
/* يمكنك تخصيص أنماط الشريط الجانبي هنا */
</style>
