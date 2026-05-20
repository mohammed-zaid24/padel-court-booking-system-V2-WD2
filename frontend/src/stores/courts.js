import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '../api'

export const useCourtStore = defineStore('courts', () => {
  const courts = ref([])
  const totalCourts = ref(0)
  const isLoading = ref(false)
  const error = ref(null)

  async function fetchCourts(filters = {}) {
    isLoading.value = true
    error.value = null
    try {
      const params = new URLSearchParams()
      if (filters.name) params.append('name', filters.name)
      if (filters.location) params.append('location', filters.location)
      if (filters.limit) params.append('limit', filters.limit)
      if (filters.offset !== undefined) params.append('offset', filters.offset)

      const response = await api.get('/courts?' + params.toString())
      courts.value = response.data.data
      totalCourts.value = response.data.total
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to load courts.'
    } finally {
      isLoading.value = false
    }
  }

  async function fetchCourtById(id) {
    const response = await api.get(`/courts/${id}`)
    return response.data
  }

  async function createCourt(courtData) {
    const response = await api.post('/courts', courtData)
    return response.data
  }

  async function updateCourt(id, courtData) {
    const response = await api.put(`/courts/${id}`, courtData)
    return response.data
  }

  async function deleteCourt(id) {
    await api.delete(`/courts/${id}`)
  }

  async function fetchAvailability(courtId, date) {
    const response = await api.get(`/courts/${courtId}/availability?date=${date}`)
    return response.data
  }

  return {
    courts,
    totalCourts,
    isLoading,
    error,
    fetchCourts,
    fetchCourtById,
    createCourt,
    updateCourt,
    deleteCourt,
    fetchAvailability,
  }
})
