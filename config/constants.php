<?php
// Site configuration
define('SITE_NAME', 'Danau Toba Ticketing');
define('SITE_URL', 'http://localhost/danautoba-ticketing');

// Upload paths
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/danautoba-ticketing/uploads/');
define('HOTEL_IMAGE_PATH', 'uploads/hotel_images/');

// Booking statuses
define('STATUS_PENDING', 'pending');
define('STATUS_CONFIRMED', 'confirmed');
define('STATUS_CANCELLED', 'cancelled');
?>