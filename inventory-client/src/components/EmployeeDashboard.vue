<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps(['user']);
const emit = defineEmits(['logout']);
const API_URL = 'https://backend1-idluo06v.b4a.run/api';
const loading = ref(false);
const form = ref({
  project: '',
  activity: '',
  dateStart: '',
  timeStart: '',
  dateEnd: '',
  timeEnd: '',
  remarks: '',
  attachment: null
});

const projectOptions = ['Project Alpha', 'Maintenance Server', 'Migrasi Database', 'Audit IT'];
const activityOptions = ['Coding', 'Meeting', 'Dokumentasi', 'Bug Fixing', 'Deployment'];

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      alert("File terlalu besar! Maksimal 5MB.");
      e.target.value = "";
      return;
    }
    form.value.attachment = file;
  }
};

const submitForm = async () => {
  if (!form.value.project || !form.value.activity || !form.value.timeStart || !form.value.timeEnd) {
    return alert("Harap lengkapi kolom Project, Activity, dan Waktu Kerja!");
  }

  loading.value = true;
  try {
    const formData = new FormData();
    // Mengambil id user yang login dari props
    formData.append('user_id', props.user.id);
    formData.append('pin', props.user.pin); 
    formData.append('project_name', form.value.project);
    formData.append('activity_type', form.value.activity);
    formData.append('start_working', `${form.value.dateStart} ${form.value.timeStart}`);
    formData.append('end_working', `${form.value.dateEnd} ${form.value.timeEnd}`);
    formData.append('remarks', form.value.remarks || '');
    if (form.value.attachment) formData.append('attachment', form.value.attachment);

    await axios.post(`${API_URL}/activities`, formData, { headers: { 'Content-Type': 'multipart/form-data' }});

    alert("✅ Data Berhasil Disimpan!");
    
    form.value.activity = ''; 
    form.value.remarks = ''; 
    form.value.attachment = null;
    document.getElementById('fileInput').value = ""; 

  } catch (e) {
    alert("Gagal: " + (e.response?.data?.message || e.message));
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  const today = new Date().toISOString().split('T')[0];
  form.value.dateStart = today; 
  form.value.dateEnd = today;
});
</script>

<template>
  <div class="glass-card dashboard-view">
    <div class="dash-header">
      <div class="user-info"><h1>Add Data</h1><span class="badge">User: {{ user.name }}</span></div>
      <button @click="emit('logout')" class="icon-btn logout">🚪</button>
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

      <div class="form-row">
        <div class="form-col">
          <label class="label-field">Start</label>
          <input type="time" v-model="form.timeStart" class="modern-input">
        </div>
        <div class="form-col">
          <label class="label-field">End</label>
          <input type="time" v-model="form.timeEnd" class="modern-input">
        </div>
      </div>

      <label class="label-field">Remarks</label>
      <textarea v-model="form.remarks" class="modern-textarea" placeholder="Catatan tambahan..."></textarea>

      <label class="label-field">Attachment (Optional)</label>
      <input type="file" id="fileInput" @change="handleFileChange" class="modern-input file-input">

      <button @click="submitForm" :disabled="loading" class="modern-btn btn-report">
        {{ loading ? 'Saving...' : 'Save Data' }}
      </button>
    </div>
  </div>
</template>