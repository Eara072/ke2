<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';


const API_URL = 'http://localhost:8000/api';

// STATE UMUM
const users = ref([]);
const currentUser = ref(null);
const loading = ref(false);
const loginForm = ref({ userId: '', pin: '' });

// STATE ADMIN (DENGAN FILTER TANGGAL)
const adminData = ref({ 
  date_label: '', 
  summary: [], 
  details: [] 
});

const filterDate = ref({
  start: '',
  end: ''
});

// FORM KARYAWAN (TETAP SAMA)
const activityText = ref('');
const form = ref({
  project: '', activity: '', dateStart: '', timeStart: '', 
  dateEnd: '', timeEnd: '', achieveType: '', achieveTotal: 0, 
  attachment: null, remarks: ''
});

const projectOptions = ['Project Alpha', 'Maintenance Server', 'Migrasi Database', 'Audit IT'];
const activityOptions = ['Coding', 'Meeting', 'Dokumentasi', 'Bug Fixing', 'Deployment'];
const achieveOptions = ['Modul Selesai', 'Halaman Dibuat', 'Bug Fixed', 'Jam Kerja'];

const isMagicLink = ref(false);
const magicUserName = ref('');

// 1. FETCH INITIAL
const fetchUsersAndCheckUrl = async () => {
  try {
    const res = await axios.get(`${API_URL}/users`);
    users.value = res.data;
    const urlParams = new URLSearchParams(window.location.search);
    const uidFromUrl = urlParams.get('uid');
    if (uidFromUrl) {
      const targetUser = users.value.find(u => u.id == uidFromUrl);
      if (targetUser) {
        loginForm.value.userId = targetUser.id;
        magicUserName.value = targetUser.name;
        isMagicLink.value = true;
      }
    }
  } catch (e) { alert("Gagal koneksi backend."); }
};

// 2. LOGIN
const handleLogin = async () => {
  if (!loginForm.value.userId || !loginForm.value.pin) return alert("Lengkapi data!");
  loading.value = true;
  try {
    const res = await axios.post(`${API_URL}/login`, { 
      user_id: loginForm.value.userId, pin: loginForm.value.pin 
    });
    currentUser.value = res.data.user;
    localStorage.setItem('user_session', JSON.stringify(res.data.user));
    
    // Set Default Tanggal Hari Ini
    const today = new Date().toISOString().split('T')[0];
    
    // Default Filter Admin
    filterDate.value.start = today;
    filterDate.value.end = today;
    
    // Default Form Karyawan
    form.value.dateStart = today; 
    form.value.dateEnd = today;

    if (currentUser.value.role === 'admin') fetchAdminStats();
    window.history.replaceState({}, document.title, "/"); 

  } catch (e) { alert(e.response?.data?.message || "Login Gagal"); } 
  finally { loading.value = false; }
};

const handleLogout = () => { 
  currentUser.value = null; loginForm.value.pin = '';
  isMagicLink.value = false;
  localStorage.removeItem('user_session');
  fetchUsersAndCheckUrl();
};

// 3. ADMIN: FETCH STATS (DENGAN PARAMETER TANGGAL)
const fetchAdminStats = async () => {
  try {
    const res = await axios.get(`${API_URL}/admin/stats`, {
      params: {
        start_date: filterDate.value.start,
        end_date: filterDate.value.end
      }
    });
    adminData.value = res.data;
  } catch (e) { console.error(e); }
};

// 4. KARYAWAN: HANDLE FILE
const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) form.value.attachment = file;
};

// 5. KARYAWAN: SUBMIT FORM
const submitForm = async () => {
  if (!form.value.project || !form.value.activity || !form.value.timeStart || !form.value.timeEnd) {
    return alert("Lengkapi data wajib!");
  }
  loading.value = true;
  try {
    const formData = new FormData();
    formData.append('user_id', currentUser.value.id);
    formData.append('pin', currentUser.value.pin);
    formData.append('project_name', form.value.project);
    formData.append('activity_type', form.value.activity);
    formData.append('start_working', `${form.value.dateStart} ${form.value.timeStart}`);
    formData.append('end_working', `${form.value.dateEnd} ${form.value.timeEnd}`);
    formData.append('achievement_type', form.value.achieveType || '-');
    formData.append('achievement_total', form.value.achieveTotal || 0);
    formData.append('remarks', form.value.remarks || '');
    if (form.value.attachment) formData.append('attachment', form.value.attachment);

    await axios.post(`${API_URL}/activities`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    alert("✅ Data Berhasil Disimpan!");
    form.value.activity = ''; form.value.remarks = '';
    document.getElementById('fileInput').value = ""; 
  } catch (e) { alert("Gagal: " + e.message); } 
  finally { loading.value = false; }
};

onMounted(() => {
  fetchUsersAndCheckUrl();
  const session = localStorage.getItem('user_session');
  if (session) {
    currentUser.value = JSON.parse(session);
    // Set Default Date saat refresh
    const today = new Date().toISOString().split('T')[0];
    filterDate.value.start = today;
    filterDate.value.end = today;
    
    if (currentUser.value.role === 'admin') fetchAdminStats();
  }
});
</script>

<template>
  <div class="modern-container">
    <transition name="fade" mode="out-in">
      
      <!-- LOGIN -->
      <div v-if="!currentUser" class="glass-card login-view" key="login">
        <div class="brand-logo"><span class="logo-icon"></span><h2>Laporan Produksi</h2></div>
        <p class="subtitle" v-if="isMagicLink">Selamat Datang Kembali,</p>
        <p class="subtitle" v-else>Masuk Sistem Monitoring</p>

        <div class="form-wrapper">
          <div v-if="isMagicLink" class="user-locked">
            <h3>{{ magicUserName }}</h3>
            <button @click="isMagicLink = false; loginForm.userId = ''" class="btn-change-user">Bukan Anda?</button>
          </div>
          <div v-else class="select-wrapper">
            <select v-model="loginForm.userId" class="modern-input select-box">
              <option disabled value="">Pilih Nama Anda...</option>
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} {{ user.role === 'admin' ? '(Admin)' : '' }}</option>
            </select>
          </div>
          <input type="password" v-model="loginForm.pin" class="modern-input pin-field" maxlength="6" placeholder="******" autofocus>
          <button @click="handleLogin" :disabled="loading" class="modern-btn btn-login">{{ loading ? 'Masuk...' : 'Masuk Sistem' }}</button>
        </div>
      </div>

      <!-- ADMIN DASHBOARD (DENGAN FILTER TANGGAL) -->
      <div v-else-if="currentUser.role === 'admin'" class="glass-card admin-view" key="admin">
        <div class="dash-header">
          <div class="user-info">
            <h1>📊 Project Dashboard</h1>
            <span class="badge badge-admin">{{ adminData.date_label }}</span>
          </div>
          <button @click="handleLogout" class="icon-btn logout">🚪</button>
        </div>

        <!-- FITUR BARU: FILTER TANGGAL -->
        <div class="filter-bar">
          <div class="filter-group">
            <label>Dari:</label>
            <input type="date" v-model="filterDate.start" class="filter-input">
          </div>
          <div class="filter-group">
            <label>Sampai:</label>
            <input type="date" v-model="filterDate.end" class="filter-input">
          </div>
          <button @click="fetchAdminStats" class="btn-filter">🔎 Tampilkan Data</button>
        </div>

        <!-- RINGKASAN -->
        <div class="summary-grid">
          <div v-for="(sum, index) in adminData.summary" :key="index" class="summary-card">
            <div class="sum-label">{{ sum.type }}</div>
            <div class="sum-value">{{ sum.total_time }}</div>
          </div>
          <div v-if="adminData.summary.length === 0" class="summary-card empty">
            Tidak ada aktivitas pada rentang tanggal ini.
          </div>
        </div>

        <!-- TABEL -->
        <div class="admin-table-container">
          <table class="report-table">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Project</th>
                <th>Aktifitas</th>
                <th class="text-center">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in adminData.details" :key="row.id">
                <td style="font-size:12px; color:#666;">{{ row.date }}</td>
                <td style="font-weight:bold; color:#14532d;">{{ row.employee }}</td>
                <td><span class="project-tag">{{ row.project }}</span></td>
                <td>{{ row.activity }}</td>
                <td class="text-center">
                  <span class="duration-badge">{{ row.duration_str }}</span>
                </td>
              </tr>
              <tr v-if="adminData.details.length === 0">
                <td colspan="5" style="text-align:center; padding: 30px; color:#999;">Belum ada data.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- KARYAWAN DASHBOARD (TETAP) -->
      <div v-else class="glass-card dashboard-view" key="employee">
        <div class="dash-header">
          <div class="user-info"><h1>Add Data</h1><span class="badge">User: {{ currentUser.name }}</span></div>
          <button @click="handleLogout" class="icon-btn logout">🚪</button>
        </div>
        <div class="form-scroll-area">
          <label class="label-field">Project <span class="req">*</span></label>
          <div class="select-wrapper">
            <select v-model="form.project" class="modern-input select-box">
              <option value="" disabled>Select Project</option>
              <option v-for="p in projectOptions" :key="p" :value="p">{{ p }}</option>
            </select>
          </div>
          <label class="label-field">Activity <span class="req">*</span></label>
          <div class="select-wrapper">
            <select v-model="form.activity" class="modern-input select-box">
              <option value="" disabled>Select Activity</option>
              <option v-for="a in activityOptions" :key="a" :value="a">{{ a }}</option>
            </select>
          </div>
          <label class="label-field">Start Working <span class="req">*</span></label>
          <div class="form-row">
            <input type="date" v-model="form.dateStart" class="modern-input form-col">
            <input type="time" v-model="form.timeStart" class="modern-input form-col">
          </div>
          <label class="label-field">End Working <span class="req">*</span></label>
          <div class="form-row">
            <input type="date" v-model="form.dateEnd" class="modern-input form-col">
            <input type="time" v-model="form.timeEnd" class="modern-input form-col">
          </div>
          <label class="label-field">Today's Achievements</label>
          <div class="form-row">
            <div class="select-wrapper form-col">
              <select v-model="form.achieveType" class="modern-input select-box">
                <option value="" disabled>Type</option>
                <option v-for="ac in achieveOptions" :key="ac" :value="ac">{{ ac }}</option>
              </select>
            </div>
            <input type="number" v-model="form.achieveTotal" class="modern-input form-col" placeholder="Total">
          </div>
          <label class="label-field">Attachment (Optional)</label>
          <input type="file" id="fileInput" @change="handleFileChange" class="modern-input file-input">
          <label class="label-field">Remarks</label>
          <textarea v-model="form.remarks" class="modern-textarea" placeholder="Catatan tambahan..."></textarea>
          <button @click="submitForm" :disabled="loading" class="modern-btn btn-report">{{ loading ? 'Saving...' : 'Save Data' }}</button>
        </div>
      </div>

    </transition>
  </div>
</template>
<style src="./assets/style.css"></style>