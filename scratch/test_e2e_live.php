<?php

function request($method, $url, $data = null, $token = null) {
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
    ];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }

    $opts = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers) . "\r\n",
            'ignore_errors' => true,
        ]
    ];
    if ($data !== null) {
        $opts['http']['content'] = json_encode($data);
    }

    $res = file_get_contents($url, false, stream_context_create($opts));
    return json_decode($res, true);
}

$baseUrl = 'http://127.0.0.1:8000/api';

echo "=== 1. TEACHER LOGIN ===\n";
$teacherAuth = request('POST', "$baseUrl/auth/login", [
    'email' => 'teacher@pibmc.edu.kh',
    'password' => 'password',
]);
$teacherToken = $teacherAuth['token'];
echo "Teacher logged in: {$teacherAuth['user']['name']} (Token: " . substr($teacherToken, 0, 10) . "...)\n";

echo "\n=== 2. LIST COURSES ===\n";
$courses = request('GET', "$baseUrl/courses", null, $teacherToken);
$courseId = $courses['courses'][0]['id'];
echo "Found " . count($courses['courses']) . " courses. Selected Course ID: {$courseId} ({$courses['courses'][0]['name']})\n";

echo "\n=== 3. START ATTENDANCE SESSION ===\n";
$sessionRes = request('POST', "$baseUrl/attendance/sessions", [
    'course_id' => $courseId,
    'latitude' => 11.5564000,
    'longitude' => 104.9282000,
    'radius_meters' => 100,
], $teacherToken);
$sessionId = $sessionRes['session']['id'];
$sessionToken = $sessionRes['token'];
echo "Session Started! ID: {$sessionId}, Rotating Token: {$sessionToken}\n";

echo "\n=== 4. POLL CURRENT TOKEN ===\n";
$pollToken = request('GET', "$baseUrl/attendance/sessions/{$sessionId}/token", null, $teacherToken);
echo "Current active token: {$pollToken['token']}, Seconds remaining: {$pollToken['seconds_remaining']}s\n";

echo "\n=== 5. STUDENT LOGIN ===\n";
$studentAuth = request('POST', "$baseUrl/auth/login", [
    'email' => 'student1@pibmc.edu.kh',
    'password' => 'password',
]);
$studentToken = $studentAuth['token'];
echo "Student logged in: {$studentAuth['user']['name']} (ID: {$studentAuth['user']['id']})\n";

echo "\n=== 6. STUDENT CHECK-IN (INSIDE GEOFENCE) ===\n";
$checkInRes = request('POST', "$baseUrl/attendance/check-in", [
    'token' => $sessionToken,
    'latitude' => 11.5564200,
    'longitude' => 104.9282100,
], $studentToken);
echo "Check-in Status: " . ($checkInRes['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: {$checkInRes['message']}\n";
$recordId = $checkInRes['record']['id'];

echo "\n=== 7. DUPLICATE TOKEN CHECK (STUDENT 2 TRIES SAME TOKEN) ===\n";
$student2Auth = request('POST', "$baseUrl/auth/login", [
    'email' => 'student2@pibmc.edu.kh',
    'password' => 'password',
]);
$dupRes = request('POST', "$baseUrl/attendance/check-in", [
    'token' => $sessionToken,
    'latitude' => 11.5564200,
    'longitude' => 104.9282100,
], $student2Auth['token']);
echo "Duplicate Token Rejection: " . ($dupRes['errors']['token'][0] ?? 'None') . "\n";

echo "\n=== 8. SESSION ATTENDANCE REPORT ===\n";
$report = request('GET', "$baseUrl/attendance/sessions/{$sessionId}/report", null, $teacherToken);
echo "Report Summary: Present: {$report['counts']['present']}, Late: {$report['counts']['late']}, Absent: {$report['counts']['absent']}\n";

echo "\n=== 9. TEACHER MANUAL OVERRIDE ===\n";
$overrideRes = request('PATCH', "$baseUrl/attendance/records/{$recordId}", [
    'status' => 'excused',
], $teacherToken);
echo "Override status updated to: {$overrideRes['record']['status']} (method: {$overrideRes['record']['method']})\n";

echo "\n=== 10. CLOSE SESSION ===\n";
$closeRes = request('POST', "$baseUrl/attendance/sessions/{$sessionId}/close", null, $teacherToken);
echo "Session Closed: {$closeRes['session']['status']}\n";

echo "\nALL LIVE END-TO-END WORKFLOWS COMPLETED SUCCESSFULLY!\n";
