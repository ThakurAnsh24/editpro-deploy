<?php
/**
 * EditPro - Simple Ping Endpoint
 * Returns 200 OK for server status checks
 */

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);

