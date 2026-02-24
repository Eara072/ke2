<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['login-success']);
const API_URL = 'https://imperialdramon.infinityfree.me/api';

const users = ref([]);
const loading = ref(false);

// State Form Login menggunakan Username (Teks)
const loginForm = ref({ username: '', pin: '' });
const isMagicLink = ref(false);
const magicUserName = ref('');

const fetchUsersAndCheckUrl = async () => {
  try {
    const res = await axios.get(`${API_URL}/users`);
    users.value = res.data;
    const urlParams = new URLSearchParams(window.location.search);
    const uidFromUrl = urlParams.get('uid');
    if (uidFromUrl) {
      const targetUser = users.value.find(u => u.id == uidFromUrl);
      if (targetUser) {
        loginForm.value.username = targetUser.name;
        magicUserName.value = targetUser.name;
        isMagicLink.value = true;
      }
    }
  } catch (e) { alert("Gagal koneksi backend."); }
};

const handleLogin = async () => {
  if (!loginForm.value.username || !loginForm.value.pin) return alert("Lengkapi data login!");
  
  loading.value = true;
  try {
    // Mengirim payload 'username' ke Lumen
    const res = await axios.post(`${API_URL}/login`, { 
      username: loginForm.value.username, 
      pin: loginForm.value.pin 
    });
    
    emit('login-success', res.data.user);
    
  } catch (e) { 
    alert(e.response?.data?.message || "Login Gagal. Cek Nama dan PIN."); 
  } finally { 
    loading.value = false; 
  }
};

onMounted(() => {
  fetchUsersAndCheckUrl();
});
</script>

<template>
  <div class="glass-card login-view">
    <div class="brand-logo"><span class="logo-icon">🌿</span><h2>E-Monitor</h2></div>
    <p class="subtitle" v-if="isMagicLink">Selamat Datang Kembali,</p>
    <p class="subtitle" v-else>Masuk Sistem Monitoring</p>

    <div class="form-wrapper">
      <div v-if="isMagicLink" class="user-locked">
        <h3>{{ magicUserName }}</h3>
        <button @click="isMagicLink = false; loginForm.username = ''" class="btn-change-user">Bukan Anda?</button>
      </div>
      <div v-else class="input-wrapper">
        <input 
          type="text" 
          v-model="loginForm.username" 
          class="modern-input" 
          placeholder="Ketik Nama Anda..."
          required
        />
      </div>
      <input type="password" v-model="loginForm.pin" class="modern-input pin-field" maxlength="6" placeholder="******" autofocus>
      <button @click="handleLogin" :disabled="loading" class="modern-btn btn-login">
        {{ loading ? 'Memverifikasi...' : 'Masuk Sistem' }}
      </button>
    </div>
  </div>
</template>