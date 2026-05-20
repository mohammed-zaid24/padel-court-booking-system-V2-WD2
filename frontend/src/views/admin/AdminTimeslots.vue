<template>
  <div>
    <router-link to="/admin" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to dashboard</router-link>
    <h1 class="mb-4">Manage Timeslots</h1>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title">Select Court &amp; Date</h5>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Court</label>
            <select v-model="selectedCourtId" class="form-select" @change="loadTimeslots">
              <option value="">-- Select court --</option>
              <option v-for="c in courts" :key="c.id" :value="c.id">{{ c.name }} ({{ c.location }})</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" v-model="selectedDate" class="form-control" @change="loadTimeslots" />
          </div>
        </div>
      </div>
    </div>

    <!-- Add / Edit Timeslot Form -->
    <div v-if="selectedCourtId && selectedDate" class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title">{{ editingSlot ? 'Edit Timeslot' : 'Add Timeslot' }}</h5>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Start Time</label>
            <input type="time" v-model="form.start_time" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">End Time</label>
            <input type="time" v-model="form.end_time" class="form-control" />
          </div>
          <div class="col-md-4 d-flex align-items-end gap-2">
            <button class="btn btn-primary" @click="saveSlot">
              {{ editingSlot ? 'Update' : 'Add Slot' }}
            </button>
            <button v-if="editingSlot" class="btn btn-outline-secondary" @click="cancelEdit">Cancel</button>
          </div>
        </div>
        <div v-if="formError" class="alert alert-danger mt-3">{{ formError }}</div>
      </div>
    </div>

    <!-- Timeslots List -->
    <div v-if="selectedCourtId && selectedDate">
      <div v-if="isLoading" class="text-center py-4">
        <div class="spinner-border text-primary"></div>
      </div>

      <div v-else-if="timeslots.length === 0" class="text-muted text-center py-4">
        No timeslots for this court and date. Add one above.
      </div>

      <div v-else class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="slot in timeslots" :key="slot.id">
              <td>{{ slot.id }}</td>
              <td>{{ slot.start_time?.slice(0, 5) }}</td>
              <td>{{ slot.end_time?.slice(0, 5) }}</td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1" @click="startEdit(slot)">Edit</button>
                <button class="btn btn-sm btn-danger" @click="removeSlot(slot.id)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="text-muted text-center py-4">
      Select a court and date above to view or manage timeslots.
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject } from 'vue'
import api from '../../api'
import { useCourtStore } from '../../stores/courts'

const courtStore = useCourtStore()
const showNotification = inject('showNotification')

const courts = ref([])
const selectedCourtId = ref('')
const selectedDate = ref('')
const timeslots = ref([])
const isLoading = ref(false)
const editingSlot = ref(null)
const formError = ref('')

const form = reactive({ start_time: '', end_time: '' })

onMounted(async () => {
  await courtStore.fetchCourts()
  courts.value = courtStore.courts
})

async function loadTimeslots() {
  if (!selectedCourtId.value || !selectedDate.value) return
  isLoading.value = true
  try {
    const res = await api.get(`/timeslots?court_id=${selectedCourtId.value}&date=${selectedDate.value}`)
    timeslots.value = res.data.data
  } catch {
    timeslots.value = []
  } finally {
    isLoading.value = false
  }
}

function startEdit(slot) {
  editingSlot.value = slot.id
  form.start_time = slot.start_time?.slice(0, 5)
  form.end_time = slot.end_time?.slice(0, 5)
}

function cancelEdit() {
  editingSlot.value = null
  form.start_time = ''
  form.end_time = ''
  formError.value = ''
}

async function saveSlot() {
  formError.value = ''
  if (!form.start_time || !form.end_time) {
    formError.value = 'Start time and end time are required.'
    return
  }

  try {
    if (editingSlot.value) {
      await api.put(`/timeslots/${editingSlot.value}`, {
        slot_date: selectedDate.value,
        start_time: form.start_time + ':00',
        end_time: form.end_time + ':00',
      })
      showNotification('Timeslot updated.')
    } else {
      await api.post('/timeslots', {
        court_id: parseInt(selectedCourtId.value),
        slot_date: selectedDate.value,
        start_time: form.start_time + ':00',
        end_time: form.end_time + ':00',
      })
      showNotification('Timeslot added.')
    }
    cancelEdit()
    await loadTimeslots()
  } catch (err) {
    const data = err.response?.data
    formError.value = data?.errors?.join(' ') || data?.error || 'Failed to save timeslot.'
  }
}

async function removeSlot(id) {
  if (!confirm('Delete this timeslot?')) return
  try {
    await api.delete(`/timeslots/${id}`)
    showNotification('Timeslot deleted.')
    await loadTimeslots()
  } catch (err) {
    showNotification(err.response?.data?.error || 'Failed to delete.', 'error')
  }
}
</script>
