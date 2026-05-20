<template>
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark rounded mx-3 mt-3">
      <div class="container-fluid">
        <router-link class="navbar-brand fw-bold" to="/">Padel Booking</router-link>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <!-- Public / User links -->
            <template v-if="!authStore.isAdmin">
              <li class="nav-item">
                <router-link class="nav-link" to="/">Home</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/courts">Courts</router-link>
              </li>
            </template>

            <!-- User-only links -->
            <template v-if="authStore.isLoggedIn && !authStore.isAdmin">
              <li class="nav-item">
                <router-link class="nav-link" to="/my-bookings">My Bookings</router-link>
              </li>
            </template>

            <!-- Admin links -->
            <template v-if="authStore.isAdmin">
              <li class="nav-item">
                <router-link class="nav-link" to="/admin">Dashboard</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/admin/courts">Courts</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/admin/timeslots">Timeslots</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/admin/bookings">Bookings</router-link>
              </li>
            </template>
          </ul>

          <ul class="navbar-nav">
            <template v-if="authStore.isLoggedIn">
              <li class="nav-item">
                <span class="nav-link text-light">
                  {{ authStore.userName }}
                  <span class="badge bg-secondary ms-1">{{ authStore.user?.role }}</span>
                </span>
              </li>
              <li class="nav-item">
                <button class="btn btn-outline-light btn-sm" @click="handleLogout">Logout</button>
              </li>
            </template>
            <template v-else>
              <li class="nav-item">
                <router-link class="btn btn-outline-light btn-sm me-2" to="/login">Login</router-link>
              </li>
              <li class="nav-item">
                <router-link class="btn btn-light btn-sm" to="/register">Register</router-link>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

function handleLogout() {
  authStore.logout()
  router.push('/login')
}
</script>
