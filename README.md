# Padel Court Booking System

Web Development 2 project — a Padel court booking app built as a Vue.js frontend with a PHP REST API backend. Everything runs in Docker.

**Student:** Mohammed Zaid
**Student Number:** 708467 IT2B

## How to run it

You need Docker Desktop installed and running. Then from this folder:
docker compose up --build

The first build takes a couple of minutes. Once it's done, open:

- App: http://localhost:5173
- API: http://localhost:8080/api
- phpMyAdmin: http://localhost:8081

To stop:
docker compose down

To stop and wipe the database:
docker compose down -v

## Login info

| Role  | Email             | Password |
|-------|-------------------|----------|
| Admin | admin@padel.local | admin123 |
| User  | user@padel.local  | user123  |

You can also register a new user from the Register page.

phpMyAdmin login is `root` / `rootpass`.

## What's in here
padel-booking/
├── backend/             PHP REST API (Composer + JWT)
├── frontend/            Vue 3 app (Vite + Pinia + Vue Router + Bootstrap)
├── db/schema.sql        Database schema + seed data
├── database.sql         Same file, copied to root for the submission
├── docker-compose.yml   Runs the whole stack
└── README.md

## Features

**Users can:**
- Register and log in
- Browse courts and search by name/location
- Pick a date and see which timeslots are free
- Book a court
- See their bookings (upcoming and past)
- Cancel their own bookings

**Admins can:**
- Add, edit, and delete courts
- Add, edit, and delete timeslots per court
- See all bookings across all users (with filtering and pagination)
- Delete any booking

**Business rules enforced by the backend:**
- No double-booking (same court + same date + same timeslot)
- No booking in the past
- Clear error messages when something fails

## API endpoints

Everything is under `/api`. Protected routes need an `Authorization: Bearer <token>` header.

**Auth**
- POST `/api/auth/register`
- POST `/api/auth/login`

**Courts**
- GET `/api/courts` (supports `?name=`, `?location=`, `?limit=`, `?offset=`)
- GET `/api/courts/:id`
- GET `/api/courts/:id/availability?date=YYYY-MM-DD`
- POST `/api/courts` (admin)
- PUT `/api/courts/:id` (admin)
- DELETE `/api/courts/:id` (admin)

**Timeslots**
- GET `/api/timeslots?court_id=X[&date=YYYY-MM-DD]`
- GET `/api/timeslots/:id`
- POST `/api/timeslots` (admin)
- PUT `/api/timeslots/:id` (admin)
- DELETE `/api/timeslots/:id` (admin)

**Bookings**
- GET `/api/bookings` (admin, supports filtering + pagination)
- GET `/api/bookings/my` (logged-in user)
- GET `/api/bookings/:id`
- POST `/api/bookings`
- PUT `/api/bookings/:id`
- DELETE `/api/bookings/:id`

POST endpoints return the created object including the new ID (HTTP 201). Errors come back as JSON with proper status codes (400, 401, 403, 404, 422, 500).

## Tech stack

- **Frontend:** Vue 3 (Composition API), Vue Router, Pinia, Axios, Bootstrap 5
- **Backend:** PHP 8.2, Apache, Composer (PSR-4 autoload), firebase/php-jwt for JWT
- **Database:** MySQL 8.0
- **Containers:** Docker Compose

The backend follows an MVC layout: Controllers → Services → Repositories → Models. JWT auth is checked in the backend on every protected route, and admin-only routes also check the user's role server-side so the frontend can't be bypassed.

## Notes on ports

If you already have something running on 8080, 8081, or 3306, change the host port in `docker-compose.yml` (the number on the left of the colon) and use that new port in the browser. The frontend talks to the backend over Docker's internal network, so changing the host port for the backend doesn't break anything.

## AI disclosure

I used Claude (the chat AI) to help me debug Docker build issues, write parts of the README, and speed up some of the boilerplate. The design, use case, API shape, and business rules all came from my own proposal, and I went through the code to make sure I understand every part of it.