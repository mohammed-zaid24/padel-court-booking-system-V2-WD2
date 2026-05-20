<template>
  <div>
    <h1 class="mb-4">My Bookings</h1>

    <div v-if="bookingStore.isLoading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else-if="bookingStore.error" class="alert alert-danger">{{ bookingStore.error }}</div>

    <div v-else-if="bookingStore.myBookings.length === 0" class="text-center py-5">
      <p class="text-muted">You don't have any bookings yet.</p>
      <router-link to="/courts" class="btn btn-primary">Find a court</router-link>
    </div>

    <div v-else>
      <!-- Upcoming bookings -->
      <h2 v-if="upcomingBookings.length > 0" class="h5 mb-3">Upcoming</h2>
      <div class="table-responsive mb-4" v-if="upcomingBookings.length > 0">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-dark">
            <tr>
              <th>Court</th>
              <th>Location</th>
              <th>Date</th>
              <th>Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in upcomingBookings" :key="b.booking_id">
              <td>{{ b.court_name }}</td>
              <td>{{ b.court_location }}</td>
              <td>{{ b.date }}</td>
              <td>{{ b.start_time?.slice(0,5) }} - {{ b.end_time?.slice(0,5) }}</td>
              <td>
                <button class="btn btn-sm btn-outline-danger" @click="cancelBooking(b.booking_id)">
                  Cancel
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Past bookings -->
      <h2 v-if="pastBookings.length > 0" class="h5 mb-3 text-muted">Past</h2>
      <div class="table-responsive" v-if="pastBookings.length > 0">
        <table class="table table-striped table-bordered align-middle opacity-75">
          <thead>
            <tr>
              <th>Court</th>
              <th>Location</th>
              <th>Date</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in pastBookings" :key="b.booking_id">
              <td>{{ b.court_name }}</td>
              <td>{{ b.court_location }}</td>
              <td>{{ b.date }}</td>
              <td>{{ b.start_time?.slice(0,5) }} - {{ b.end_time?.slice(0,5) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, inject } from 'vue'
import { useBookingStore } from '../stores/bookings'

const bookingStore = useBookingStore()
const showNotification = inject('showNotification')

const today = new Date().toISOString().split('T')[0]

const upcomingBookings = computed(() =>
  bookingStore.myBookings.filter(b => b.date >= today)
)

const pastBookings = computed(() =>
  bookingStore.myBookings.filter(b => b.date < today)
)

onMounted(() => {
  bookingStore.fetchMyBookings()
})

async function cancelBooking(id) {
  if (!confirm('Are you sure you want to cancel this booking?')) return

  try {
    await bookingStore.deleteBooking(id)
    showNotification('Booking cancelled.')
    await bookingStore.fetchMyBookings()
  } catch (err) {
    showNotification(err.response?.data?.error || 'Failed to cancel.', 'error')
  }
}
</script>
