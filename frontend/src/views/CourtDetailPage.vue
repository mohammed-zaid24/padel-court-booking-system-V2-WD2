<template>
  <div>
    <router-link to="/courts" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to courts</router-link>

    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <template v-else-if="court">
      <h1 class="mb-2">{{ court.name }}</h1>
      <p class="text-muted mb-4"><span class="me-1">&#128205;</span>{{ court.location }}</p>

      <!-- Date picker -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title">Check Availability</h5>
          <div class="row align-items-end">
            <div class="col-md-4">
              <label for="date" class="form-label">Select date</label>
              <input type="date" id="date" v-model="selectedDate" class="form-control" :min="today" @change="loadAvailability" />
            </div>
          </div>
        </div>
      </div>

      <!-- Availability slots -->
      <div v-if="selectedDate && availability" class="card shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title">Available slots for {{ selectedDate }}</h5>

          <div v-if="slotsLoading" class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary"></div>
          </div>

          <div v-else-if="availability.slots.length === 0" class="text-muted">
            No timeslots available for this date.
          </div>

          <div v-else class="row row-cols-2 row-cols-md-4 g-3">
            <div v-for="slot in availability.slots" :key="slot.id" class="col">
              <div class="card text-center" :class="slot.is_booked ? 'slot-booked' : 'slot-available'" @click="!slot.is_booked && bookSlot(slot)">
                <div class="card-body py-3">
                  <strong>{{ slot.start_time.slice(0,5) }} - {{ slot.end_time.slice(0,5) }}</strong>
                  <br />
                  <small :class="slot.is_booked ? 'text-danger' : 'text-success'">
                    {{ slot.is_booked ? 'Booked' : 'Available' }}
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Booking confirmation -->
      <div v-if="bookingSuccess" class="alert alert-success">
        Booking confirmed! <router-link to="/my-bookings">View my bookings</router-link>
      </div>
      <div v-if="bookingError" class="alert alert-danger">{{ bookingError }}</div>
    </template>

    <div v-else class="alert alert-warning">Court not found.</div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { useRoute } from 'vue-router'
import { useCourtStore } from '../stores/courts'
import { useBookingStore } from '../stores/bookings'
import { useAuthStore } from '../stores/auth'

const props = defineProps({ id: [String, Number] })
const route = useRoute()
const courtStore = useCourtStore()
const bookingStore = useBookingStore()
const authStore = useAuthStore()
const showNotification = inject('showNotification')

const court = ref(null)
const isLoading = ref(true)
const selectedDate = ref('')
const availability = ref(null)
const slotsLoading = ref(false)
const bookingSuccess = ref(false)
const bookingError = ref('')

const today = new Date().toISOString().split('T')[0]

onMounted(async () => {
  try {
    court.value = await courtStore.fetchCourtById(props.id || route.params.id)
  } catch {
    court.value = null
  } finally {
    isLoading.value = false
  }
})

async function loadAvailability() {
  if (!selectedDate.value) return
  slotsLoading.value = true
  bookingSuccess.value = false
  bookingError.value = ''
  try {
    availability.value = await courtStore.fetchAvailability(court.value.id, selectedDate.value)
  } catch {
    availability.value = { slots: [] }
  } finally {
    slotsLoading.value = false
  }
}

async function bookSlot(slot) {
  if (!authStore.isLoggedIn) {
    bookingError.value = 'Please login to book a court.'
    return
  }

  bookingError.value = ''
  try {
    await bookingStore.createBooking({
      court_id: court.value.id,
      date: selectedDate.value,
      timeslot_id: slot.id,
    })
    bookingSuccess.value = true
    showNotification('Booking created successfully!')
    await loadAvailability() // refresh slots
  } catch (err) {
    bookingError.value = err.response?.data?.error || 'Failed to create booking.'
  }
}
</script>
