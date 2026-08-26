<?php
/**
 * Script para resetear contraseñas de los usuarios demo.
 * Uso: php reset-passwords.php
 *      o accede en navegador: http://127.0.0.1:8023/reset-passwords.php
 */

// Configuración de conexión (ajusta si es necesario)
$db_host = 'localhost';
$db_name = 'tpv_minimarket';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage() . "\n");
}

// Usuarios y contraseñas a establecer
$usuarios = [
    'admin'   => 'admin123',
    'gerente' => 'gerente123',
    'cajero'  => 'cajero123',
];

$esWeb = !empty($_SERVER['HTTP_HOST']);
$br = $esWeb ? '<br>' : "\n";

if ($esWeb) {
    echo '<style>body{font-family:Arial;background:#0f172a;color:#e2e8f0;padding:30px;line-height:1.7;}
          .ok{color:#10b981;}.err{color:#ef4444;}.box{background:#1e293b;padding:20px;border-radius:10px;max-width:600px;}
          h1{color:#10b981;}code{background:#334155;padding:2px 8px;border-radius:4px;}</style>
          <div class="box"><h1>🔐 Reset de Contraseñas</h1>';
}

echo "Conectado a base de datos: $db_name$br$br";

foreach ($usuarios as $username => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $stmt = $pdo->prepare("UPDATE users SET password = ?, activo = 1 WHERE username = ?");
    $stmt->execute([$hash, $username]);

    if ($stmt->rowCount() > 0) {
        $msg = $esWeb
            ? "<span class='ok'>✓</span> <strong>$username</strong> → password: <code>$password</code>"
            : "✓ $username → password: $password";
        echo $msg . $br;
    } else {
        $msg = $esWeb
            ? "<span class='err'>✗</span> Usuario <strong>$username</strong> no encontrado"
            : "✗ Usuario $username no encontrado";
        echo $msg . $br;
    }
}

echo $br . "✅ Listo. Ya puedes iniciar sesión con las credenciales mostradas." . $br;

if ($esWeb) {
    echo $br . "<a href='/login' style='display:inline-block;margin-top:15px;background:#10b981;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;'>Ir al Login →</a>";
    echo "<br><br><strong style='color:#ef4444;'>⚠️ ELIMINA este archivo después de usarlo por seguridad.</strong>";
    echo "</div>";
}
