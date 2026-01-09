<?php
/**
 * Send a standardized JSON response
 *
 * @param bool $success - true if the request succeeded
 * @param string $message - message to send to frontend
 * @param mixed|null $data - optional data payload (array, object, etc.)
 */
function sendResponse($success, $message, $data = null) {
    // Set content type to JSON
    header('Content-Type: application/json');

    // Build response array
    $response = [
        'success' => $success,
        'message' => $message
    ];

    // Include data if provided
    if ($data !== null) {
        $response['data'] = $data;
    }

    // Output JSON
    echo json_encode($response);

    // Stop further execution
    exit;
}
?>
