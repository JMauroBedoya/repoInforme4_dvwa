<?php
// Configuración
$host     = "192.168.0.7";
$database = "dvwa";         // variable definida correctamente
$username = "dvwa";
$password = "password";

// Conexión usando sqlsrv (Microsoft Drivers for PHP for SQL Server)
$serverName = $host;
$connectionInfo = [
    "Database"     => $database,
    "UID"          => $username,
    "PWD"          => $password,
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    // No mostrar errores crudos al usuario en producción; aquí es demostrativo.
    die("Error de conexión: " . htmlspecialchars(print_r(sqlsrv_errors(), true), ENT_QUOTES, 'UTF-8'));
}

// Consulta (no hay entrada del usuario en este ejemplo; si la hubiera, usar Prepared Statements)
$sql = "SELECT first_name /*, password */ FROM users"; // NO seleccionar password si no es necesario
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error en la consulta: " . htmlspecialchars(print_r(sqlsrv_errors(), true), ENT_QUOTES, 'UTF-8'));
}

// Mostrar resultados con escape para prevenir XSS
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $first_name = $row['first_name'] ?? '';
    echo htmlspecialchars($first_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "<br />\n";
}

// Liberar y cerrar
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
