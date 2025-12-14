<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Ganti sesuai port backend Anda
const API_URL = 'http://localhost:8000/api';

// DATA STATE
const users = ref([]);
const currentUser = ref(null);
const loading = ref(false);
const loginForm = ref({ userId: '', pin: '' });
const activityText = ref('');

// STATE KHUSUS ADMIN
const adminData = ref({ date: '', list: [] });

// STATE KHUSUS MAGIC LINK (WA)
const isMagicLink = ref(false);
const magicUserName = ref('');

// 1. FUNGSI UTAMA: AMBIL DATA USER & CEK URL
const fetchUsersAndCheckUrl = async () => {
  try {
    const res = await axios.get(`${API_URL}/users`);
    users.value = res.data;

    // --- LOGIKA DETEKSI LINK WA ---
    const urlParams = new URLSearchParams(window.location.search);
    const uidFromUrl = urlParams.get('uid');

    if (uidFromUrl) {
      const targetUser = users.value.find(u => u.id == uidFromUrl);
      if (targetUser) {
        // Jika ID ketemu, kunci form login
        loginForm.value.userId = targetUser.id;
        magicUserName.value = targetUser.name;
        isMagicLink.value = true; 
      }
    }
    // ------------------------------

  } catch (e) { 
    alert("Gagal terhubung ke server Backend."); 
  }
};

// 2. LOGIN SYSTEM
const handleLogin = async () => {
  if(!loginForm.value.userId || !loginForm.value.pin) return alert("Isi PIN!");

  loading.value = true;
  try {
    const res = await axios.post(`${API_URL}/login`, {
      user_id: loginForm.value.userId, pin: loginForm.value.pin
    });
    
    currentUser.value = res.data.user;
    localStorage.setItem('user_session', JSON.stringify(res.data.user));

    if (res.data.user.role === 'admin') fetchAdminStats();

    // Hapus ?uid= dari URL agar bersih
    window.history.replaceState({}, document.title, "/"); 

  } catch (e) {
    alert(e.response?.data?.message || "PIN Salah.");
  } finally { loading.value = false; }
};

// 3. LOGOUT
const handleLogout = () => {
  currentUser.value = null; 
  loginForm.value.pin = '';
  isMagicLink.value = false; // Reset mode
  localStorage.removeItem('user_session');
  
  // Ambil ulang data user untuk reset form
  fetchUsersAndCheckUrl();
};

// 4. KIRIM LAPORAN (EMPLOYEE)
const submitActivity = async () => {
  loading.value = true;
  try {
    await axios.post(`${API_URL}/activities`, {
      user_id: currentUser.value.id, description: activityText.value, pin: currentUser.value.pin 
    });
    alert("✅ Laporan Berhasil!");
    activityText.value = ''; 
  } catch (e) { alert("Gagal: " + e.message); } 
  finally { loading.value = false; }
};

// 5. AMBIL DATA DASHBOARD (ADMIN)
const fetchAdminStats = async () => {
  try {
    const res = await axios.get(`${API_URL}/admin/stats`);
    adminData.value.date = res.data.date;
    adminData.value.list = res.data.data;
  } catch (e) { console.error(e); }
};

// JALAN SAAT WEB DIBUKA
onMounted(() => {
  fetchUsersAndCheckUrl(); 
  const session = localStorage.getItem('user_session');
  if (session) {
    currentUser.value = JSON.parse(session);
    if (currentUser.value.role === 'admin') fetchAdminStats();
  }
});
</script>

<template>
  <div class="modern-container">
    <transition name="fade" mode="out-in">
      
      <!-- TAMPILAN 1: HALAMAN LOGIN -->
      <div v-if="!currentUser" class="glass-card login-view" key="login">
        <div class="brand-logo">
          <span class="logo-icon">🚀</span>
          <h2>E-Monitor</h2>
        </div>
        
        <!-- Judul Berubah Jika dari WA -->
        <p class="subtitle" v-if="isMagicLink">Selamat Datang Kembali,</p>
        <p class="subtitle" v-else>Masuk Sistem Monitoring</p>

        <div class="form-wrapper">
          
          <!-- KONDISI A: DARI WA (MAGIC LINK) -->
          <!-- Dropdown hilang, cuma muncul nama teks -->
          <div v-if="isMagicLink" class="user-locked">
            <h3>{{ magicUserName }}</h3>
            <button @click="isMagicLink = false; loginForm.userId = ''" class="btn-change-user">
              Bukan Anda? Ganti Akun
            </button>
          </div>

          <!-- KONDISI B: LOGIN BIASA -->
          <div v-else class="select-wrapper">
            <label>Identitas Pengguna</label>
            <select v-model="loginForm.userId" class="modern-input select-box">
              <option disabled value="">Pilih Nama Anda...</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
          
          <!-- INPUT PIN (Selalu Muncul) -->
          <label>PIN Keamanan</label>
          <input 
            type="password" 
            v-model="loginForm.pin" 
            class="modern-input pin-field" 
            maxlength="6" 
            placeholder="• • • • • •" 
            autofocus
          >

          <button @click="handleLogin" :disabled="loading" class="modern-btn btn-login">
            {{ loading ? 'Memverifikasi...' : 'Masuk Sistem' }}
          </button>
        </div>
      </div>

      <!-- TAMPILAN 2: DASHBOARD ADMIN -->
      <div v-else-if="currentUser.role === 'admin'" class="glass-card admin-view" key="admin">
        <div class="dash-header">
          <div class="user-info">
            <h1>👮 Dashboard Superadmin</h1>
            <span class="badge badge-admin">Rekap: {{ adminData.date }}</span>
          </div>
          <button @click="handleLogout" class="icon-btn logout">🚪</button>
        </div>
        
        <div class="admin-table-container">
          <div class="table-actions">
            <span class="info-label">Status Laporan Hari Ini:</span>
            <button @click="fetchAdminStats" class="btn-refresh">🔄 Refresh</button>
          </div>
          <table class="report-table">
            <thead>
              <tr><th>Nama</th><th>Shift</th><th>Pagi</th><th>Sore</th><th>Total</th></tr>
            </thead>
            <tbody>
              <tr v-for="emp in adminData.list" :key="emp.id">
                <td style="font-weight:bold">{{ emp.name }}</td>
                <td>{{ emp.shift }}</td>
                <td class="text-center"><span :class="emp.status_pagi ? 'tag tag-success':'tag tag-danger'">{{ emp.status_pagi ? '✅' : '❌' }}</span></td>
                <td class="text-center"><span :class="emp.status_sore ? 'tag tag-success':'tag tag-danger'">{{ emp.status_sore ? '✅' : '❌' }}</span></td>
                <td class="text-center">{{ emp.total_laporan }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAMPILAN 3: DASHBOARD KARYAWAN -->
      <div v-else class="glass-card dashboard-view" key="employee">
        <div class="dash-header">
          <div class="user-info">
            <h1>{{ currentUser.name }}</h1>
            <span class="badge">Shift: {{ currentUser.start_time }} - {{ currentUser.end_time }}</span>
          </div>
          <button @click="handleLogout" class="icon-btn logout">🚪</button>
        </div>
        
        <div class="dash-body">
          <div class="status-indicator"><span class="pulse"></span> Monitoring Aktif</div>
          <label class="label-activity">Kegiatan Saat Ini</label>
          <textarea v-model="activityText" class="modern-textarea" placeholder="Contoh: Sedang membalas chat customer..."></textarea>
          <button @click="submitActivity" :disabled="loading || !activityText" class="modern-btn btn-report">{{ loading ? 'Mengirim...' : 'Kirim Laporan' }}</button>
          <p class="info-text">Target: Wajib Lapor 2x Sehari (Pagi & Sore).</p>
        </div>
      </div>

    </transition>
  </div>
</template>

<style>
/* STYLE GLOBAL */
body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%); color: #333; }
.modern-container { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }

/* KARTU UTAMA */
.glass-card { background: rgba(255, 255, 255, 0.98); width: 100%; max-width: 450px; padding: 40px; border-radius: 24px; box-shadow: 0 20px 40px rgba(22, 163, 74, 0.1); border: 1px solid #bbf7d0; }
.admin-view { max-width: 850px; } /* Admin lebih lebar */

/* INPUT FORM */
.modern-input, .modern-textarea { width: 100%; padding: 14px; border: 2px solid #e2e8f0; border-radius: 12px; background: #ffffff !important; opacity: 1 !important; color: #333; font-size: 16px; margin-bottom: 20px; box-sizing: border-box; transition: border 0.3s; }
.modern-input:focus, .modern-textarea:focus { border-color: #22c55e; outline: none; }
.pin-field { text-align: center; letter-spacing: 5px; font-weight: bold; }
.select-wrapper { position: relative; }

/* TOMBOL */
.modern-btn { width: 100%; padding: 16px; border: none; border-radius: 12px; font-size: 16px; font-weight: bold; cursor: pointer; background: #16a34a; color: white; transition: 0.3s; }
.modern-btn:hover { background: #15803d; transform: translateY(-2px); }
.modern-btn:disabled { background: #cbd5e1; transform: none; cursor: not-allowed; }

/* STYLE UNTUK USER YANG DIKUNCI (MAGIC LINK) */
.user-locked { text-align: center; margin-bottom: 20px; padding: 20px; background: #f0fdf4; border-radius: 12px; border: 1px solid #bbf7d0; }
.user-locked h3 { margin: 0 0 5px 0; color: #166534; font-size: 20px; }
.btn-change-user { background: none; border: none; color: #dc2626; font-size: 13px; cursor: pointer; text-decoration: underline; margin-top: 5px; }

/* DASHBOARD ADMIN */
.dash-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.badge-admin { background: #e0f2fe !important; color: #0369a1 !important; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; }
.table-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.admin-table-container { margin-top: 10px; overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; }
.report-table { width: 100%; border-collapse: collapse; background: white; }
.report-table th, .report-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
.report-table th { background: #f0fdf4; color: #15803d; font-weight: 700; }
.text-center { text-align: center !important; }
.tag { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block; }
.tag-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.tag-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

/* LAIN-LAIN */
.icon-btn { background: #fee2e2; border: none; border-radius: 50%; width: 42px; height: 42px; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; }
.status-indicator { display: flex; align-items: center; justify-content: center; gap: 8px; color: #15803d; font-weight: 600; margin-bottom: 20px; font-size: 14px; background: #f0fdf4; padding: 10px; border-radius: 8px;}
.pulse { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; box-shadow: 0 0 0 rgba(34, 197, 94, 0.4); animation: pulse 2s infinite; }
.btn-refresh { background: #3b82f6; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; }
.select-box { -webkit-appearance: none; appearance: none; background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23333%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E"); background-repeat: no-repeat; background-position: right 15px center; background-size: 12px; }
@keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); } 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }
</style>