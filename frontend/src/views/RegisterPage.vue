<template>
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h1 class="h3 mb-4 text-center">Create Account</h1>

          <div v-if="errorMsg" class="alert alert-danger">{{ errorMsg }}</div>

          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" v-model="name" class="form-control" placeholder="Your name" />
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" v-model="email" class="form-control" placeholder="your@email.com" />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" v-model="password" class="form-control" placeholder="Min. 6 characters" @keyup.enter="handleRegister" />
          </div>

          <button class="btn btn-primary w-100" @click="handleRegister" :disabled="isLoading">
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span>
            {{ isLoading ? 'Creating...' : 'Register' }}
          </button>

          <p class="text-center text-muted mt-3 mb-0">
            Already have an account? <router-link to="/login">Login here</router-link>
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

const name = ref('')
const email = ref('')
const password = ref('')
const errorMsg = ref('')
const isLoading = ref(false)

async function handleRegister() {
  errorMsg.value = ''
  isLoading.value = true

  try {
    await authStore.register(name.value, email.value, password.value)
    showNotification('Account created successfully!')
    router.push('/')
  } catch (err) {
    const data = err.response?.data
    errorMsg.value = data?.errors?.join(' ') || data?.error || 'Registration failed.'
  } finally {
    isLoading.value = false
  }
}
</script>
