<template>
  <div>
    <NavBar />
    <main class="container py-4">
      <div v-if="notification" class="alert" :class="notification.type === 'success' ? 'alert-success' : 'alert-danger'" role="alert">
        {{ notification.message }}
      </div>
      <router-view />
    </main>
    <footer class="container py-3 text-muted">
      <small>&copy; {{ new Date().getFullYear() }} Padel Booking</small>
    </footer>
  </div>
</template>

<script setup>
import { ref, provide } from 'vue'
import NavBar from './components/NavBar.vue'

const notification = ref(null)

function showNotification(message, type = 'success') {
  notification.value = { message, type }
  setTimeout(() => {
    notification.value = null
  }, 4000)
}

provide('showNotification', showNotification)
</script>
