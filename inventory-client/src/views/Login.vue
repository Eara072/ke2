<script setup>
import { ref, onMounted } from 'vue';

// Import komponen-komponen pecahan
import LoginForm from '../components/LoginForm.vue';
import AdminDashboard from '../components/AdminDashboard.vue';
import EmployeeDashboard from '../components/EmployeeDashboard.vue';

const currentUser = ref(null);

// Fungsi saat berhasil login
const onLoginSuccess = (user) => {
  currentUser.value = user;
  localStorage.setItem('user_session', JSON.stringify(user));
};

// Fungsi saat logout
const onLogout = () => {
  currentUser.value = null;
  localStorage.removeItem('user_session');
};

// Cek session saat pertama kali web dibuka
onMounted(() => {
  const session = localStorage.getItem('user_session');
  if (session) {
    currentUser.value = JSON.parse(session);
  }
});
</script>

<template>
  <div class="modern-container">
    <transition name="fade" mode="out-in">
      
      <!-- KOMPONEN FORM LOGIN -->
      <LoginForm 
        v-if="!currentUser" 
        @login-success="onLoginSuccess" 
        key="login" 
      />

      <!-- KOMPONEN DASHBOARD ADMIN -->
      <AdminDashboard 
        v-else-if="currentUser.role === 'admin' || currentUser.role === 'superadmin'" 
        :user="currentUser" 
        @logout="onLogout" 
        key="admin" 
      />

      <!-- KOMPONEN DASHBOARD USER/KARYAWAN -->
      <EmployeeDashboard 
        v-else 
        :user="currentUser" 
        @logout="onLogout" 
        key="employee" 
      />

    </transition>
  </div>
</template>