<?php
header('Content-Type: application/json');
require_once '../../config/koneksi.php';

$role_id = $_GET['role_id'] ?? null;
$id_levelmenu = $_GET['id_levelmenu'] ?? null;
$where = [];
$params = [];
$types = '';

if ($role_id !== null && $role_id !== '') {
    $where[] = "role_id = ?";
    $params[] = $role_id;
    $types .= "i";
}

if ($id_levelmenu !== null && $id_levelmenu !== '') {
    $where[] = "id_levelmenu = ?";
    $params[] = $id_levelmenu;
    $types .= "i";
}

$where_clause = '';
if (count($where) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $where);
}

$sql = "SELECT * FROM menu_level $where_clause ORDER BY role_id ASC, id_levelmenu ASC";

if (count($params) > 0) {
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $koneksi->query($sql);
}

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    'status' => 'success',
    'data' => $data,
    'total_rows' => count($data)
]);
