<?php
$json = file_get_contents('firebase-service-account.json');
echo "Raw JSON content first 200 characters:<br>";
echo htmlspecialchars(substr($json, 0, 200)) . "<br><br>";

echo "File size: " . strlen($json) . " bytes<br>";
echo "First 20 bytes as hex: " . bin2hex(substr($json, 0, 20)) . "<br><br>";

$data = json_decode($json, true);

if ($data === null) {
    echo "JSON Error: " . json_last_error_msg() . "<br>";
    echo "JSON Error Code: " . json_last_error() . "<br>";

    // Try to find the exact position of the error
    $lines = explode("\n", $json);
    for ($i = 0; $i < min(10, count($lines)); $i++) {
        echo "Line " . ($i + 1) . ": " . htmlspecialchars($lines[$i]) . "<br>";
    }
} else {
    echo "JSON is valid!<br>";
    echo "Project ID: " . $data['project_id'] . "<br>";
    echo "Client Email: " . $data['client_email'] . "<br>";
}
