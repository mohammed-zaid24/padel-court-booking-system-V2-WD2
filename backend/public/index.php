<?php

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set JSON content type for all responses
header('Content-Type: application/json; charset=utf-8');

// CORS headers for Vue frontend
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Get the request path and method
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Read JSON body for POST/PUT requests
$body = json_decode(file_get_contents('php://input'), true) ?? [];

// ─── Auth Routes ───────────────────────────────────────────────
if ($path === '/api/auth/register' && $method === 'POST') {
    $controller = new App\Controllers\AuthController();
    $controller->register($body);

} elseif ($path === '/api/auth/login' && $method === 'POST') {
    $controller = new App\Controllers\AuthController();
    $controller->login($body);

// ─── Courts Routes ─────────────────────────────────────────────
} elseif ($path === '/api/courts' && $method === 'GET') {
    $controller = new App\Controllers\CourtController();
    $controller->index();

} elseif (preg_match('#^/api/courts/(\d+)$#', $path, $matches) && $method === 'GET') {
    $controller = new App\Controllers\CourtController();
    $controller->getById((int)$matches[1]);

} elseif ($path === '/api/courts' && $method === 'POST') {
    $controller = new App\Controllers\CourtController();
    $controller->create($body);

} elseif (preg_match('#^/api/courts/(\d+)$#', $path, $matches) && $method === 'PUT') {
    $controller = new App\Controllers\CourtController();
    $controller->update((int)$matches[1], $body);

} elseif (preg_match('#^/api/courts/(\d+)$#', $path, $matches) && $method === 'DELETE') {
    $controller = new App\Controllers\CourtController();
    $controller->delete((int)$matches[1]);

// ─── Court Availability ────────────────────────────────────────
} elseif (preg_match('#^/api/courts/(\d+)/availability$#', $path, $matches) && $method === 'GET') {
    $controller = new App\Controllers\CourtController();
    $controller->availability((int)$matches[1]);

// ─── Timeslots Routes ──────────────────────────────────────────
} elseif ($path === '/api/timeslots' && $method === 'GET') {
    $controller = new App\Controllers\TimeslotController();
    $controller->index();

} elseif (preg_match('#^/api/timeslots/(\d+)$#', $path, $matches) && $method === 'GET') {
    $controller = new App\Controllers\TimeslotController();
    $controller->getById((int)$matches[1]);

} elseif ($path === '/api/timeslots' && $method === 'POST') {
    $controller = new App\Controllers\TimeslotController();
    $controller->create($body);

} elseif (preg_match('#^/api/timeslots/(\d+)$#', $path, $matches) && $method === 'PUT') {
    $controller = new App\Controllers\TimeslotController();
    $controller->update((int)$matches[1], $body);

} elseif (preg_match('#^/api/timeslots/(\d+)$#', $path, $matches) && $method === 'DELETE') {
    $controller = new App\Controllers\TimeslotController();
    $controller->delete((int)$matches[1]);

// ─── Bookings Routes ───────────────────────────────────────────
} elseif ($path === '/api/bookings/my' && $method === 'GET') {
    $controller = new App\Controllers\BookingController();
    $controller->myBookings();

} elseif ($path === '/api/bookings' && $method === 'GET') {
    $controller = new App\Controllers\BookingController();
    $controller->index();

} elseif (preg_match('#^/api/bookings/(\d+)$#', $path, $matches) && $method === 'GET') {
    $controller = new App\Controllers\BookingController();
    $controller->getById((int)$matches[1]);

} elseif ($path === '/api/bookings' && $method === 'POST') {
    $controller = new App\Controllers\BookingController();
    $controller->create($body);

} elseif (preg_match('#^/api/bookings/(\d+)$#', $path, $matches) && $method === 'PUT') {
    $controller = new App\Controllers\BookingController();
    $controller->update((int)$matches[1], $body);

} elseif (preg_match('#^/api/bookings/(\d+)$#', $path, $matches) && $method === 'DELETE') {
    $controller = new App\Controllers\BookingController();
    $controller->delete((int)$matches[1]);

// ─── 404 ───────────────────────────────────────────────────────
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}
