<template>
  <div>
    <router-link to="/admin" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to dashboard</router-link>
    <h1 class="mb-4">All Bookings</h1>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Filter by date</label>
            <input type="date" v-model="filterDate" class="form-control" @change="loadBookings" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Filter by court</label>
            <select v-model="filterCourtId" class="form-select" @change="loadBookings">
              <option value="">All courts</option>
              <option v-for="c in courts" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Sort by</label>
            <select v-model="sortField" class="form-select" @change="loadBookings">
              <option value="date">Date</option>
              <option value="created_at">Created</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Order</label>
            <select v-model="sortOrder" class="form-select" @change="loadBookings">
              <option value="DESC">Newest first</option>
              <option value="ASC">Oldest first</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="bookingStore.isLoading" class="text-center py-4">
      <div class="spinner-border text-primary"></div>
    </div>

    <!-- Table -->
    <div v-else-if="bookingStore.allBookings.length > 0">
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Court</th>
              <th>Date</th>
              <th>Time</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in bookingStore.allBookings" :key="b.booking_id">
              <td>{{ b.booking_id }}</td>
              <td>{{ b.user_name || b.user_id }}</td>
              <td>{{ b.court_name || b.court_id }}</td>
              <td>{{ b.date }}</td>
              <td>{{ b.start_time?.slice(0, 5) }} - {{ b.end_time?.slice(0, 5) }}</td>
              <td>{{ b.created_at ? b.created_at.slice(0, 10) : '-' }}</td>
              <td>
                <button class="btn btn-sm btn-danger" @click="removeBooking(b.booking_id)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav v-if="totalPages > 1" class="d-flex justify-content-center">
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="goToPage(currentPage - 1)">&laquo;</button>
          </li>
          <li v-for="p in totalPages" :key="p" class="page-item" :class="{ active: p === currentPage }">
            <button class="page-link" @click="goToPage(p)">{{ p }}</button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="goToPage(currentPage + 1)">&raquo;</button>
          </li>
        </ul>
      </nav>
    </div>

    <p v-else class="text-muted text-center py-4">No bookings found.</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useBookingStore } from '../../stores/bookings'
import { useCourtStore } from '../../stores/courts'

const bookingStore = useBookingStore()
const courtStore = useCourtStore()
const showNotification = inject('showNotification')

const courts = ref([])
const filterDate = ref('')
const filterCourtId = ref('')
const sortField = ref('date')
const sortOrder = ref('DESC')
const currentPage = ref(1)
const perPage = 10

const totalPages = computed(() => Math.max(1, Math.ceil(bookingStore.totalBookings / perPage)))

onMounted(async () => {
  await courtStore.fetchCourts()
  courts.value = courtStore.courts
  loadBookings()
})

function loadBookings() {
  const filters = {
    sort: sortField.value,
    order: sortOrder.value,
    limit: perPage,
    offset: (currentPage.value - 1) * perPage,
  }
  if (filterDate.value) filters.date = filterDate.value
  if (filterCourtId.value) filters.court_id = filterCourtId.value

  bookingStore.fetchAllBookings(filters)
}

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  loadBookings()
}

async function removeBooking(id) {
  if (!confirm('Delete this booking?')) return
  try {
    await bookingStore.deleteBooking(id)
    showNotification('Booking deleted.')
    loadBookings()
  } catch (err) {
    showNotification(err.response?.data?.error || 'Failed to delete.', 'error')
  }
}
</script>
