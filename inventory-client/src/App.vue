<template>
  <div class="container">
    <h1>📦 Inventory Management</h1>
    
    <div v-if="loading" class="loading">Memuat data...</div>

    <table v-else>
      <thead>
        <tr>
          <th>Nama Barang</th>
          <th>SKU</th>
          <th>Min. Stock</th>
          <th>Stok Saat Ini</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id" :class="{ 'danger-row': isLowStock(product) }">
          <td>{{ product.name }}</td>
          <td>{{ product.sku }}</td>
          <td>{{ product.min_stock }}</td>
          <td>
            <input type="number" v-model.number="product.quantity" class="qty-input">
            <span v-if="isLowStock(product)" class="badge-alert">!</span>
          </td>
          <td>
            <button @click="updateStock(product)" :disabled="updating">
              {{ updating ? '...' : 'Update' }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// STATE
const products = ref([]);
const loading = ref(false);
const updating = ref(false);

// GANTI URL INI SESUAI PORT LUMEN KAMU (Biasanya localhost:8000)
const API_URL = 'http://localhost:8000/api/products'; 

// LOGIC: Cek apakah stok kritis
const isLowStock = (product) => {
  return product.quantity <= product.min_stock;
};

// GET DATA
const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await axios.get(API_URL);
    products.value = response.data;
  } catch (error) {
    console.error("Gagal ambil data:", error);
    alert("Gagal mengambil data. Pastikan Lumen berjalan di port 8000 dan CORS sudah diaktifkan.");
  } finally {
    loading.value = false;
  }
};

// UPDATE DATA
const updateStock = async (product) => {
  updating.value = true;
  try {
    await axios.put(`${API_URL}/${product.id}`, {
      quantity: product.quantity
    });
    
    alert('✅ Stok berhasil diupdate!');
    
    // Cek logic frontend untuk memberi tahu user
    if (isLowStock(product)) {
      alert('⚠️ Peringatan: Stok menipis! Notifikasi WhatsApp sedang dikirim oleh Server.');
    }
    
    // Refresh data untuk memastikan sinkronisasi
    fetchProducts(); 
  } catch (error) {
    console.error(error);
    alert('❌ Gagal update stok via API.');
  } finally {
    updating.value = false;
  }
};

// JALANKAN SAAT LOAD
onMounted(() => {
  fetchProducts();
});
</script>

<style>
/* CSS Sederhana */
.container { max-width: 800px; margin: 40px auto; font-family: sans-serif; }
h1 { text-align: center; color: #333; }

table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background-color: #f4f4f4; }

.qty-input { width: 60px; padding: 5px; text-align: center; }

button { background: #28a745; color: white; border: none; padding: 8px 12px; cursor: pointer; border-radius: 4px; }
button:hover { background: #218838; }
button:disabled { background: #ccc; }

/* Highlight Merah untuk Stok Habis */
.danger-row { background-color: #ffebee; border-left: 5px solid red; }
.danger-row input { border: 1px solid red; color: red; font-weight: bold; }
.badge-alert { color: red; font-weight: bold; margin-left: 5px; }

.loading { text-align: center; margin-top: 20px; color: #666; }
</style>