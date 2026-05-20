import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '../api'

export const useBookingStore = defineStore('bookings', () => {
  const myBookings = ref([])
  const allBookings = ref([])
  const totalBookings = ref(0)
  const isLoading = ref(false)
  const error = ref(null)

  async function fetchMyBookings() {
    isLoading.value = true
    error.value = null
    try {
      const response = await api.get('/bookings/my')
      myBookings.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to load bookings.'
    } finally {
      isLoading.value = false
    }
  }

  async function fetchAllBookings(filters = {}) {
    isLoading.value = true
    error.value = null
    try {
      const params = new URLSearchParams()
      if (filters.date) params.append('date', filters.date)
      if (filters.court_id) params.append('court_id', filters.court_id)
      if (filters.sort) params.append('sort', filters.sort)
      if (filters.order) params.append('order', filters.order)
      if (filters.limit) params.append('limit', filters.limit)
      if (filters.offset !== undefined) params.append('offset', filters.offset)

      const response = await api.get('/bookings?' + params.toString())
      allBookings.value = response.data.data
      totalBookings.value = response.data.total
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to load bookings.'
    } finally {
      isLoading.value = false
    }
  }

  async function createBooking(bookingData) {
    const response = await api.post('/bookings', bookingData)
    return response.data
  }

  async function updateBooking(id, bookingData) {
    const response = await api.put(`/bookings/${id}`, bookingData)
    return response.data
  }

  async function deleteBooking(id) {
    await api.delete(`/bookings/${id}`)
  }

  return {
    myBookings,
    allBookings,
    totalBookings,
    isLoading,
    error,
    fetchMyBookings,
    fetchAllBookings,
    createBooking,
    updateBooking,
    deleteBooking,
  }
})
