import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import HomePage from '../views/HomePage.vue'
import LoginPage from '../views/LoginPage.vue'
import RegisterPage from '../views/RegisterPage.vue'
import CourtsPage from '../views/CourtsPage.vue'
import CourtDetailPage from '../views/CourtDetailPage.vue'
import MyBookingsPage from '../views/MyBookingsPage.vue'
import AdminDashboard from '../views/admin/AdminDashboard.vue'
import AdminCourts from '../views/admin/AdminCourts.vue'
import AdminTimeslots from '../views/admin/AdminTimeslots.vue'
import AdminBookings from '../views/admin/AdminBookings.vue'

const routes = [
  { path: '/', name: 'home', component: HomePage },
  { path: '/login', name: 'login', component: LoginPage, meta: { guest: true } },
  { path: '/register', name: 'register', component: RegisterPage, meta: { guest: true } },
  { path: '/courts', name: 'courts', component: CourtsPage },
  { path: '/courts/:id', name: 'court-detail', component: CourtDetailPage, props: true },
  {
    path: '/my-bookings',
    name: 'my-bookings',
    component: MyBookingsPage,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminDashboard,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/courts',
    name: 'admin-courts',
    component: AdminCourts,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/timeslots',
    name: 'admin-timeslots',
    component: AdminTimeslots,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/bookings',
    name: 'admin-bookings',
    component: AdminBookings,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Redirect guests away from auth pages if already logged in
  if (to.meta.guest && authStore.isLoggedIn) {
    return next(authStore.isAdmin ? '/admin' : '/')
  }

  // Require authentication
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    return next('/login')
  }

  // Require admin role
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return next('/')
  }

  next()
})

export default router
