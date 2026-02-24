<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Import komponen pecahan yang baru
import AdminAddUser from './AddAccount.vue';
import AdminSettings from './Setting.vue';

const props = defineProps(['user']);
const emit = defineEmits(['logout']);
const API_URL = 'https://imperialdramon.infinityfree.me/api';

// State untuk mengatur tab yang aktif (default: dashboard)
const activeTab = ref('dashboard');

// === STATE KHUSUS DASHBOARD ===
const adminData = ref({ 
  date_label: '', 
  summary: [], 
  details: [] 
});
const filterDate = ref({ start: '', end: '' });

// FETCH STATISTIK DASHBOARD
const fetchAdminStats = async () => {
  try {
    const res = await axios.get(`${API_URL}/admin/stats`, {
      params: { start_date: filterDate.value.start, end_date: filterDate.value.end }
    });
    adminData.value = res.data;
  } catch (e) { 
    console.error(e); 
  }
};

onMounted(() => {
  const today = new Date().toISOString().split('T')[0];
  filterDate.value.start = today; 
  filterDate.value.end = today;
  fetchAdminStats();
});
</script>

<template>
  <div class="glass-card admin-view">
    <div class="dash-header">
      <div class="user-info">
        <!-- Judul berubah dinamis berdasarkan role -->
        <h1>{{ user.role === 'superadmin' ? '👨‍💼 Super Admin Area' : '👨‍💻 Admin Area' }}</h1>
        <span class="badge badge-admin">User: {{ user.name }} ({{ user.role }})</span>
      </div>
      <button @click="emit('logout')" class="icon-btn logout">🚪</button>
    </div>

    <!-- MENU NAVIGASI TAB -->
    <div class="admin-nav-tabs" style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; overflow-x: auto;">
      
      <!-- Tab Dashboard (Bisa dilihat Admin & Superadmin) -->
      <button 
        @click="activeTab = 'dashboard'" 
        :class="['nav-tab-btn', activeTab === 'dashboard' ? 'active' : '']"
      >
        📊 Project Dashboard
      </button>

      <!-- Tab Tambah Akun (HANYA SUPERADMIN) -->
      <button 
        v-if="user.role === 'superadmin'"
        @click="activeTab = 'adduser'" 
        :class="['nav-tab-btn', activeTab === 'adduser' ? 'active' : '']"
      >
        ➕ Tambah Akun
      </button>

      <!-- Tab Setting Akun (HANYA SUPERADMIN) -->
      <button 
        v-if="user.role === 'superadmin'"
        @click="activeTab = 'settings'" 
        :class="['nav-tab-btn', activeTab === 'settings' ? 'active' : '']"
      >
        ⚙️ Setting Akun
      </button>

    </div>

    <!-- TAB 1: PROJECT DASHBOARD -->
    <div v-if="activeTab === 'dashboard'">
      <div class="filter-bar">
        <div class="filter-group">
          <label>Dari:</label> <input type="date" v-model="filterDate.start" class="filter-input">
        </div>
        <div class="filter-group">
          <label>Sampai:</label> <input type="date" v-model="filterDate.end" class="filter-input">
        </div>
        <button @click="fetchAdminStats" class="btn-filter">🔎 Tampilkan</button>
      </div>

      <div class="summary-grid">
        <div v-for="(sum, index) in adminData.summary" :key="index" class="summary-card">
          <div class="sum-label">{{ sum.type }}</div>
          <div class="sum-value">{{ sum.total_time }}</div>
        </div>
        <div v-if="adminData.summary.length === 0" class="summary-card empty">Tidak ada aktivitas.</div>
      </div>

      <div class="admin-table-container">
        <table class="report-table">
          <thead>
            <tr><th>Tanggal</th><th>Nama</th><th>Project</th><th>Aktifitas</th><th class="text-center">Total</th></tr>
          </thead>
          <tbody>
            <tr v-for="row in adminData.details" :key="row.id">
              <td style="font-size:12px; color:#666;">{{ row.date }}</td>
              <td style="font-weight:bold; color:#14532d;">{{ row.employee }}</td>
              <td><span class="project-tag">{{ row.project }}</span></td>
              <td>{{ row.activity }}</td>
              <td class="text-center"><span class="duration-badge">{{ row.duration_str }}</span></td>
            </tr>
            <tr v-if="adminData.details.length === 0">
              <td colspan="5" style="text-align:center; padding: 30px; color:#999;">Belum ada data.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 2: TAMBAH USER (Hanya dirender jika Super Admin) -->
    <AdminAddUser v-if="activeTab === 'adduser' && user.role === 'superadmin'" />

    <!-- TAB 3: SETTING AKUN (Hanya dirender jika Super Admin) -->
    <AdminSettings v-if="activeTab === 'settings' && user.role === 'superadmin'" :user="user" />

  </div>
</template>

<style scoped>
/* CSS Khusus untuk Tab Navigasi Admin */
.nav-tab-btn {
  padding: 8px 16px; 
  border: none; 
  border-radius: 6px; 
  cursor: pointer; 
  font-weight: bold; 
  background: transparent; 
  color: #475569;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.nav-tab-btn.active {
  background-color: #15803d !important;
  color: white !important;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-tab-btn:hover:not(.active) {
  background-color: #e2e8f0;
}
</style>