<?php
header('Content-Type: application/json');
session_start();


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}


require_once 'db_connection.php';


$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';


if (empty($fullname) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Full name and email are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already in use']);
        exit;
    }
    
    
    $query = "UPDATE users SET fullname = :fullname, email = :email";
    $params = [
        ':fullname' => $fullname,
        ':email' => $email,
        ':id' => $_SESSION['user_id']
    ];
  
    
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query .= ", password = :password";
        $params[':password'] = $hashedPassword;
    }
    
    $query .= " WHERE id = :id";
    
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute($params);
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>