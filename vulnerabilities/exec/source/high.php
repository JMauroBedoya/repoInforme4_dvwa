<?php 

if (isset($_POST['Submit'])) {
    // Obtener y limpiar la entrada
    $target = trim($_POST['ip']);

    // Validar la IP: solo permitir IPv4 o IPv6 válidas
    if (filter_var($target, FILTER_VALIDATE_IP)) {

        // Determinar sistema operativo
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows
            $command = escapeshellcmd('ping -n 4 ' . escapeshellarg($target));
        } else {
            // Linux / macOS
            $command = escapeshellcmd('ping -c 4 ' . escapeshellarg($target));
        }

        // Ejecutar comando de forma segura
        $output = shell_exec($command);

        // Mostrar resultado
        echo "<pre>" . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . "</pre>";

    } else {
        echo "<p style='color:red;'>❌ Dirección IP inválida.</p>";
    }
}

?>
