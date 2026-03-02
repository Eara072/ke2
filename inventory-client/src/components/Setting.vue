<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps(['user']);
const API_URL = 'https://backend1-idluo06v.b4a.run/api';

const isSaving = ref(false);
const users = ref([]);
const selectedUserId = ref(props.user.id); // Default terpilih adalah diri sendiri

// Form diset dengan data dari user yang sedang login pertama kali
const settingForm = ref({
  name: props.user.name,
  newPin: ''
});

// Ambil data semua user dari database untuk mengisi Dropdown
const fetchUsers = async () => {
  try {
    const res = await axios.get(`${API_URL}/users`);
    users.value = res.data;
  } catch (e) {
    console.error("Gagal mengambil data user", e);
  }
};

// Fungsi yang dipanggil saat dropdown pilihan user diubah
const onUserSelect = () => {
  const selected = users.value.find(u => u.id === selectedUserId.value);
  if (selected) {
    settingForm.value.name = selected.name;
    settingForm.value.newPin = ''; // Reset form PIN setiap ganti user
  }
};

const saveSettings = async () => {
  // 1. Validasi Nama
  if (!settingForm.value.name) {
    return alert("Nama tidak boleh kosong!");
  }

  // 2. Validasi Ganti PIN (Jika diisi)
  if (settingForm.value.newPin && settingForm.value.newPin.length !== 6) {
    return alert("PIN Baru harus tepat 6 digit angka!");
  }

  isSaving.value = true;
  try {
    const selected = users.value.find(u => u.id === selectedUserId.value);
    
    // Siapkan data yang akan dikirim ke backend
    const payload = {
      name: settingForm.value.name,
      role: selected.role // Role tetap sesuai aslinya
    };

    // Masukkan PIN baru ke payload jika pengguna mengisi form PIN
    if (settingForm.value.newPin) {
      payload.pin = settingForm.value.newPin;
    }

    // Tembak API Update User (Lumen) berdasarkan ID user yang dipilih
    const res = await axios.put(`${API_URL}/users/${selectedUserId.value}`, payload);

    alert(`✅ Profil ${res.data.user.name} berhasil diperbarui!`);
    
    // JIKA YANG DIEDIT ADALAH DIRI SENDIRI (Admin yang sedang login)
    if (selectedUserId.value === props.user.id) {
      localStorage.setItem('user_session', JSON.stringify(res.data.user));
      props.user.name = res.data.user.name;
      props.user.pin = res.data.user.pin;
    }

    // Update nama di dalam daftar dropdown secara langsung
    const index = users.value.findIndex(u => u.id === selectedUserId.value);
    if (index !== -1) {
      users.value[index].name = res.data.user.name;
    }

    // Bersihkan form kolom PIN setelah sukses
    settingForm.value.newPin = '';

  } catch (e) {
    alert("Gagal menyimpan pengaturan: " + (e.response?.data?.message || e.message));
  } finally {
    isSaving.value = false;
  }
};

onMounted(() => {
  fetchUsers();
});
</script>

<template>
  <div class="glass-card" style="padding: 20px; background: rgba(255,255,255,0.6);">
    <h3 style="margin-top: 0; color: #14532d; font-size: 18px; margin-bottom: 20px;">⚙️ Manajemen & Pengaturan Akun</h3>
    
    <div class="form-scroll-area">
      <!-- DROPDOWN PILIH USER -->
      <label class="label-field">Pilih Pengguna untuk Diedit</label>
      <div class="select-wrapper" style="margin-bottom: 15px;">
        <select v-model="selectedUserId" @change="onUserSelect" class="modern-input select-box">
          <option v-for="u in users" :key="u.id" :value="u.id">
            {{ u.name }} {{ u.id === user.id ? '(Akun Anda)' : '' }}
          </option>
        </select>
      </div>

      <label class="label-field">Ubah Nama Tampilan</label>
      <input type="text" v-model="settingForm.name" class="modern-input" placeholder="Ketik nama baru...">

      <label class="label-field" style="margin-top: 15px;">Reset / Ganti PIN (Opsional)</label>
      <div class="form-row">
        <div class="form-col">
          <input type="password" v-model="settingForm.newPin" class="modern-input" placeholder="Ketik PIN Baru (6 Digit)" maxlength="6">
        </div>
      </div>
      <small style="color: #64748b; margin-top: 5px; margin-bottom: 15px; display: block;">Kosongkan form PIN jika Anda tidak ingin mengubah sandi pengguna ini.</small>

      <button @click="saveSettings" :disabled="isSaving" class="modern-btn btn-report" style="margin-top: 10px;">
        {{ isSaving ? 'Menyimpan...' : '💾 Simpan Perubahan' }}
      </button>
    </div>
  </div>
</template>