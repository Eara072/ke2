<script setup>
import { ref } from 'vue';
import axios from 'axios';

const API_URL = 'https://imperialdramon.infinityfree.me/api';

const isSavingUser = ref(false);
const newUser = ref({
  name: '',
  phone: '', // Tambahan State untuk Nomor HP
  role: 'employee',
  pin: ''
});

const submitNewUser = async () => {
  if (!newUser.value.name || !newUser.value.pin || !newUser.value.phone) {
    return alert("Harap isi Nama, No. HP, dan PIN!");
  }
  if (newUser.value.pin.length !== 6) {
    return alert("PIN harus tepat 6 karakter/angka!");
  }

  isSavingUser.value = true;
  try {
    await axios.post(`${API_URL}/users`, {
      name: newUser.value.name,
      phone: newUser.value.phone, // Nomor HP ikut dikirim ke Backend
      role: newUser.value.role,
      pin: newUser.value.pin,
      is_active: 1
    });

    alert("✅ Pengguna Baru Berhasil Ditambahkan!");
    
    // Reset form
    newUser.value = { name: '', phone: '', role: 'employee', pin: '' };

  } catch (e) {
    alert("Gagal menyimpan user: " + (e.response?.data?.message || e.message));
  } finally {
    isSavingUser.value = false;
  }
};
</script>

<template>
  <div class="glass-card" style="padding: 20px; background: rgba(255,255,255,0.6);">
    <h3 style="margin-top: 0; color: #14532d; font-size: 18px; margin-bottom: 20px;">👤 Tambah Pengguna Baru</h3>
    
    <!-- Baris 1: Nama dan No HP -->
    <div class="form-row">
      <div class="form-col">
        <label class="label-field">Nama / Username <span class="req">*</span></label>
        <input type="text" v-model="newUser.name" class="modern-input" placeholder="Ketik nama...">
      </div>
      <div class="form-col">
        <label class="label-field">No. HP (WhatsApp) <span class="req">*</span></label>
        <input type="text" v-model="newUser.phone" class="modern-input" placeholder="Contoh: 08123456789">
      </div>
    </div>

    <!-- Baris 2: Role dan PIN -->
    <div class="form-row" style="margin-top: 15px;">
      <div class="form-col">
        <label class="label-field">Role <span class="req">*</span></label>
        <div class="select-wrapper">
          <select v-model="newUser.role" class="modern-input select-box">
            <option value="employee">Karyawan (Employee)</option>
            <option value="admin">Admin Biasa</option>
            <option value="superadmin">Super Admin</option>
          </select>
        </div>
      </div>
      <div class="form-col">
        <label class="label-field">PIN Login (6 Digit) <span class="req">*</span></label>
        <input type="text" v-model="newUser.pin" class="modern-input" maxlength="6" placeholder="Contoh: 123456">
      </div>
    </div>

    <!-- Tombol Simpan -->
    <button @click="submitNewUser" :disabled="isSavingUser" class="modern-btn btn-report" style="margin-top: 20px; width: 100%;">
      {{ isSavingUser ? 'Menyimpan...' : '➕ Simpan Pengguna' }}
    </button>
  </div>
</template>