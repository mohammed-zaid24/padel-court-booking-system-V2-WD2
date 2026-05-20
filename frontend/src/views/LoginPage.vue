<template>
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h1 class="h3 mb-4 text-center">Login</h1>

          <div v-if="errorMsg" class="alert alert-danger">{{ errorMsg }}</div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" v-model="email" class="form-control" placeholder="your@email.com" @keyup.enter="handleLogin" />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" v-model="password" class="form-control" placeholder="Password" @keyup.enter="handleLogin" />
          </div>

          <button class="btn btn-primary w-100" @click="handleLogin" :disabled="isLoading">
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span>
            {{ isLoading ? 'Logging in...' : 'Login' }}
          </button>

          <p class="text-center text-muted mt-3 mb-0">
            Don't have an account? <router-link to="/register">Register here</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const showNotification = inject('showNotification')

const email = ref('')
const password = ref('')
const errorMsg = ref('')
const isLoading = ref(false)

async function handleLogin() {
  errorMsg.value = ''
  isLoading.value = true

  try {
    await authStore.login(email.value, password.value)
    showNotification('Login successful!')
    router.push(authStore.isAdmin ? '/admin' : '/')
  } catch (err) {
    errorMsg.value = err.response?.data?.error || 'Login failed. Please check your credentials.'
  } finally {
    isLoading.value = false
  }
}
</script>
