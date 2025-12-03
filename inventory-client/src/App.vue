<template>
  <div class="container">
    <nav class="tabs">
      <button @click="activeTab = 'inventory'" :class="{ active: activeTab === 'inventory' }">📦 Inventory</button>
      <button @click="activeTab = 'employee'" :class="{ active: activeTab === 'employee' }">🧑‍💼 Laporan Karyawan</button>
    </nav>

    <div v-if="activeTab === 'inventory'">
      <h1>Manajemen Stok</h1>
      <p><i>...Tabel Inventory Anda...</i></p> 
    </div>

    <div v-if="activeTab === 'employee'" class="activity-box">
      <h1>📝 Lapor Kegiatan</h1>
      <p class="subtitle">Lapor setiap 10 menit agar tidak kena SP (Notif WA)!</p>

      <div class="form-group">
        <label>Nama Karyawan:</label>
        <select v-model="selectedUser">
          <option disabled value="">-- Pilih Nama Anda --</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label>Apa yang sedang dikerjakan?</label>
        <textarea v-model="activityText" rows="4" placeholder="Contoh: Sedang packing barang A..."></textarea>
      </div>

      <button @click="submitActivity" :disabled="loading" class="btn-submit">
        {{ loading ? 'Mengirim...' : 'Lapor Sekarang' }}
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const activeTab = ref('employee'); // Default tab
const users = ref([]);
const selectedUser = ref('');
const activityText = ref('');
const loading = ref(false);

const API_URL = 'http://localhost:8000/api';

// Ambil data karyawan buat dropdown
const fetchUsers = async () => {
  try {
    const res = await axios.get(`${API_URL}/users`);
    users.value = res.data;
  } catch (e) {
    console.error("Gagal ambil user", e);
  }
};

// Kirim Laporan
const submitActivity = async () => {
  if (!selectedUser.value || !activityText.value) {
    alert("Pilih nama dan isi kegiatan dulu!");
    return;
  }

  loading.value = true;
  try {
    await axios.post(`${API_URL}/activities`, {
      user_id: selectedUser.value,
      description: activityText.value
    });

    alert("✅ Laporan diterima! Anda aman dari notifikasi WA untuk 10 menit ke depan.");
    activityText.value = ''; // Reset form
  } catch (e) {
    alert("❌ Gagal lapor: " + e.message);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchUsers();
});
</script>

<style>
/* Style Tambahan */
.tabs { margin-bottom: 20px; display: flex; gap: 10px; justify-content: center; }
.tabs button { background: #eee; color: #333; }
.tabs button.active { background: #007bff; color: white; }

.activity-box { max-width: 500px; margin: 0 auto; border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
.form-group { margin-bottom: 15px; text-align: left; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
.form-group select, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
.subtitle { color: #666; font-size: 0.9em; margin-bottom: 20px; }
.btn-submit { width: 100%; background: #007bff; }
</style>