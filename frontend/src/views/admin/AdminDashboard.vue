<template>
  <div>
    <h1 class="mb-4">Admin Dashboard</h1>
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <div class="col">
        <router-link to="/admin/courts" class="text-decoration-none">
          <div class="card shadow-sm h-100">
            <div class="card-body text-center py-5">
              <h2 class="display-6">&#127934;</h2>
              <h5 class="card-title">Manage Courts</h5>
              <p class="card-text text-muted">Add, edit, or remove padel courts</p>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col">
        <router-link to="/admin/timeslots" class="text-decoration-none">
          <div class="card shadow-sm h-100">
            <div class="card-body text-center py-5">
              <h2 class="display-6">&#128339;</h2>
              <h5 class="card-title">Manage Timeslots</h5>
              <p class="card-text text-muted">Configure available time slots per court</p>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col">
        <router-link to="/admin/bookings" class="text-decoration-none">
          <div class="card shadow-sm h-100">
            <div class="card-body text-center py-5">
              <h2 class="display-6">&#128203;</h2>
              <h5 class="card-title">All Bookings</h5>
              <p class="card-text text-muted">View and manage all user bookings</p>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>
