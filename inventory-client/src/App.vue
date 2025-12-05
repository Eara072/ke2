<template>
  <div class="app-container">
    <div class="card">
      <div class="header">
        <h1>🔒 Laporan Karyawan</h1>
        <p>Silakan input kegiatan & PIN Anda.</p>
      </div>

      <div v-if="loadingData" class="loading">Mengambil data karyawan...</div>

      <div v-else class="form-container">
        
        <!-- 1. PILIH NAMA -->
        <div class="form-group">
          <label>Nama Karyawan:</label>
          <select v-model="selectedUser" class="input-field">
            <option disabled value="">-- Pilih Nama --</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} (Shift: {{ user.start_time }} - {{ user.end_time }})
            </option>
          </select>
        </div>

        <!-- 2. INPUT PIN (BARU) -->
        <div class="form-group">
          <label>PIN Keamanan:</label>
          <input 
            type="password" 
            v-model="pin" 
            class="input-field pin-input" 
            placeholder="Masukkan 6 digit PIN"
            maxlength="6"
            inputmode="numeric"
          >
        </div>

        <!-- 3. INPUT KEGIATAN -->
        <div class="form-group">
          <label>Kegiatan:</label>
          <textarea 
            v-model="activityText" 
            rows="3" 
            class="input-field"
            placeholder="Contoh: Sedang packing orderan..."
          ></textarea>
        </div>

        <button 
          @click="submitActivity" 
          :disabled="submitting || !selectedUser || !activityText || !pin" 
          class="btn-submit"
        >
          {{ submitting ? 'Memverifikasi...' : 'Kirim Laporan' }}
        </button>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api'; 

const users = ref([]);
const selectedUser = ref('');
const activityText = ref('');
const pin = ref(''); // Variable untuk PIN
const loadingData = ref(true);
const submitting = ref(false);

const fetchUsers = async () => {
  try {
    const res = await axios.get(`${API_URL}/users`);
    users.value = res.data;
  } catch (e) {
    alert("Gagal koneksi ke server.");
  } finally {
    loadingData.value = false;
  }
};

const submitActivity = async () => {
  submitting.value = true;
  try {
    // Kirim data lengkap ke Backend
    await axios.post(`${API_URL}/activities`, {
      user_id: selectedUser.value,
      description: activityText.value,
      pin: pin.value // Kirim PIN juga
    });

    alert("✅ Sukses! Laporan diterima.");
    
    // Reset Form (Penting biar orang berikutnya gak liat)
    activityText.value = '';
    pin.value = ''; 
    selectedUser.value = '';

  } catch (e) {
    // Ambil pesan error dari Backend (Contoh: "PIN Salah!")
    const msg = e.response?.data?.message || "Terjadi kesalahan sistem";
    alert(msg);
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchUsers();
});
</script>

<style>
body { margin: 0; font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; }
.app-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
.card { background: white; width: 100%; max-width: 450px; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.header { text-align: center; margin-bottom: 25px; }
.header h1 { margin: 0; color: #333; font-size: 24px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; color: #555; }
.input-field { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box; }
.pin-input { letter-spacing: 5px; font-weight: bold; text-align: center; } /* Biar PIN terlihat keren */
.input-field:focus { border-color: #1a73e8; outline: none; }
.btn-submit { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
.btn-submit:hover { background: #1557b0; }
.btn-submit:disabled { background: #ccc; cursor: not-allowed; }
.loading { text-align: center; color: #888; }
</style>