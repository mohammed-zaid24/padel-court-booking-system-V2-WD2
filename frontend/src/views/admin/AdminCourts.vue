<template>
  <div>
    <router-link to="/admin" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to dashboard</router-link>
    <h1 class="mb-4">Manage Courts</h1>

    <!-- Add Court Form -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title">{{ editingCourt ? 'Edit Court' : 'Add New Court' }}</h5>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Name</label>
            <input type="text" v-model="form.name" class="form-control" placeholder="Court name" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Location</label>
            <input type="text" v-model="form.location" class="form-control" placeholder="Location" />
          </div>
          <div class="col-md-4 d-flex align-items-end gap-2">
            <button class="btn btn-primary" @click="saveCourt">
              {{ editingCourt ? 'Update' : 'Add Court' }}
            </button>
            <button v-if="editingCourt" class="btn btn-outline-secondary" @click="cancelEdit">Cancel</button>
          </div>
        </div>
        <div v-if="formError" class="alert alert-danger mt-3">{{ formError }}</div>
      </div>
    </div>

    <!-- Courts Table -->
    <div v-if="courtStore.isLoading" class="text-center py-4">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Location</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="court in courtStore.courts" :key="court.id">
            <td>{{ court.id }}</td>
            <td>{{ court.name }}</td>
            <td>{{ court.location }}</td>
            <td>
              <button class="btn btn-sm btn-outline-primary me-1" @click="startEdit(court)">Edit</button>
              <button class="btn btn-sm btn-danger" @click="removeCourt(court.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject } from 'vue'
import { useCourtStore } from '../../stores/courts'

const courtStore = useCourtStore()
const showNotification = inject('showNotification')

const form = reactive({ name: '', location: '' })
const editingCourt = ref(null)
const formError = ref('')

onMounted(() => courtStore.fetchCourts())

function startEdit(court) {
  editingCourt.value = court.id
  form.name = court.name
  form.location = court.location
}

function cancelEdit() {
  editingCourt.value = null
  form.name = ''
  form.location = ''
}

async function saveCourt() {
  formError.value = ''
  if (!form.name || !form.location) {
    formError.value = 'Name and location are required.'
    return
  }

  try {
    if (editingCourt.value) {
      await courtStore.updateCourt(editingCourt.value, { name: form.name, location: form.location })
      showNotification('Court updated.')
    } else {
      await courtStore.createCourt({ name: form.name, location: form.location })
      showNotification('Court added.')
    }
    cancelEdit()
    await courtStore.fetchCourts()
  } catch (err) {
    formError.value = err.response?.data?.error || 'Failed to save court.'
  }
}

async function removeCourt(id) {
  if (!confirm('Delete this court? All its timeslots and bookings will also be removed.')) return
  try {
    await courtStore.deleteCourt(id)
    showNotification('Court deleted.')
    await courtStore.fetchCourts()
  } catch (err) {
    showNotification(err.response?.data?.error || 'Failed to delete.', 'error')
  }
}
</script>
