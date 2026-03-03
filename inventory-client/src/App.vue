<script setup>
// Mengimpor CSS Global (Pastikan file style.css ada di src/assets/)
import './assets/style.css';

// Mengimpor file Wadah Utama
import MainApp from './views/Login.vue';
</script>

<template>
  <MainApp />
</template>

<style>
body, html {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #e8f5e9;
  min-height: 100vh;
}
#app {
  width: 100%;
  height: 100vh;
}
</style>