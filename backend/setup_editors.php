<?php
include 'config.php';

$editors = [
    ['Divansh', 'divansh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor', 'active'],
    ['Editor 2', 'editor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor', 'active'],
];

foreach ($editors as $editor) {
    $stmt = $conn->prepare("INSERT IGNORE INTO team_members (name, username, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $editor[0], $editor[1], $editor[2], $editor[3], $editor[4]);
    $stmt->execute();
    echo "Added: " . $editor[0] . " (" . $editor[1] . ")\n";
}

echo "Test editors created. Password: password\n";
?>

