<?php

if ( isset($_COOKIE['id']) ) {
    // Leer cookie (sin concatenar nunca en la consulta)
    $id_raw = $_COOKIE['id'];
    $exists = false;

    // Validación básica: si esperas IDs numéricos, conviértelo; si no, lo tratamos como string
    $is_numeric_id = is_numeric($id_raw);
    if ($is_numeric_id) {
        $id_for_bind = (int) $id_raw;
    } else {
        // opcional: limitar longitud para evitar inputs muy largos
        $id_for_bind = substr($id_raw, 0, 100);
    }

    switch ($_DVWA['SQLI_DB']) {
        case MYSQL:
            // Usar prepared statements con mysqli
            $conn = $GLOBALS["___mysqli_ston"];
            $exists = false;

            if ($conn) {
                $sql = "SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1;";
                $stmt = mysqli_prepare($conn, $sql);
                if ($stmt) {
                    // 'i' para entero, 's' para string
                    $type = $is_numeric_id ? 'i' : 's';
                    mysqli_stmt_bind_param($stmt, $type, $id_for_bind);
                    mysqli_stmt_execute($stmt);

                    // almacenar resultado para usar num_rows
                    mysqli_stmt_store_result($stmt);
                    $exists = (mysqli_stmt_num_rows($stmt) > 0);

                    mysqli_stmt_close($stmt);
                } else {
                    // prepare falló: evitar mostrar errores al usuario
                    $exists = false;
                }
            } else {
                $exists = false;
            }

            // Si tu diseño requiere cerrar la conexión aquí, hazlo; si se usa en otras partes, no la cierres.
            // ((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
            break;

        case SQLITE:
            // Asumo SQLite3 (objeto SQLite3). Usar prepare + bindValue
            global $sqlite_db_connection;
            $exists = false;

            if ($sqlite_db_connection) {
                $sql = "SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;";
                try {
                    $stmt = $sqlite_db_connection->prepare($sql);
                    if ($stmt) {
                        if ($is_numeric_id) {
                            $stmt->bindValue(':id', $id_for_bind, SQLITE3_INTEGER);
                        } else {
                            $stmt->bindValue(':id', $id_for_bind, SQLITE3_TEXT);
                        }

                        $results = $stmt->execute();
                        if ($results !== false) {
                            $row = $results->fetchArray(SQLITE3_ASSOC);
                            $exists = ($row !== false && $row !== null);
                            $results->finalize();
                        } else {
                            $exists = false;
                        }
                        $stmt->close();
                    } else {
                        $exists = false;
                    }
                } catch (Exception $e) {
                    // No exponer detalles: fallar de forma segura
                    $exists = false;
                }
            } else {
                $exists = false;
            }

            break;
    }

    if ($exists) {
        // Feedback para el usuario
        $html .= '<pre>User ID exists in the database.</pre>';
    } else {
        // (opcional) sleep aleatorio - conservar si lo necesitas por timing attacks
        if ( rand(0,5) == 3 ) {
            sleep( rand(2,4) );
        }

        // Devolver 404
        header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found' );
        $html .= '<pre>User ID is MISSING from the database.</pre>';
    }
}

?>
