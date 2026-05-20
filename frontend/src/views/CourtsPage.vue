<template>
  <div>
    <h1 class="mb-4">Padel Courts</h1>

    <!-- Search / Filter -->
    <div class="row mb-4">
      <div class="col-md-4">
        <input type="text" v-model="searchName" class="form-control" placeholder="Search by name..." @input="debouncedSearch" />
      </div>
      <div class="col-md-4">
        <input type="text" v-model="searchLocation" class="form-control" placeholder="Filter by location..." @input="debouncedSearch" />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="courtStore.isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="courtStore.error" class="alert alert-danger">{{ courtStore.error }}</div>

    <!-- Courts Grid -->
    <div v-else class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
      <div v-for="court in courtStore.courts" :key="court.id" class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">{{ court.name }}</h5>
            <p class="card-text text-muted">
              <span class="me-1">&#128205;</span>{{ court.location }}
            </p>
            <router-link :to="`/courts/${court.id}`" class="btn btn-primary">
              View availability
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <p v-if="!courtStore.isLoading && courtStore.courts.length === 0" class="text-muted text-center py-4">
      No courts found.
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useCourtStore } from '../stores/courts'

const courtStore = useCourtStore()
const searchName = ref('')
const searchLocation = ref('')

let debounceTimer = null

function debouncedSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    loadCourts()
  }, 300)
}

function loadCourts() {
  const filters = {}
  if (searchName.value) filters.name = searchName.value
  if (searchLocation.value) filters.location = searchLocation.value
  courtStore.fetchCourts(filters)
}

onMounted(() => {
  loadCourts()
})
</script>
