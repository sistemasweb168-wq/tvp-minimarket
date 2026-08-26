<?php
/**
 * Script TEMPORAL para resetear contraseñas. ELIMINAR después de usar.
 * Acceso: http://127.0.0.1:8023/reset-passwords.php
 */

$db_host = 'localhost';
$db_name = 'tpv_minimarket';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

$usuarios = [
    'admin'   => 'admin123',
    'gerente' => 'gerente123',
    'cajero'  => 'cajero123',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reset de Contraseñas</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #064e3b, #10b981); min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; padding: 20px; }
.box { background: white; padding: 40px; border-radius: 20px; max-width: 500px; width: 100%; box-shadow: 0 30px 60px rgba(0,0,0,0.3); }
h1 { color: #059669; margin-top: 0; }
.ok { color: #10b981; font-weight: bold; }
.err { color: #ef4444; font-weight: bold; }
code { background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-family: monospace; color: #0f172a; }
.row { padding: 12px; background: #f8fafc; border-radius: 10px; margin-bottom: 8px; }
.btn { display: inline-block; margin-top: 20px; background: linear-gradient(135deg, #059669, #10b981); color: white; padding: 14px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; }
.warn { background: #fef3c7; color: #92400e; padding: 15px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #f59e0b; }
</style>
</head>
<body>
<div class="box">
<h1>🔐 Reset de Contraseñas</h1>
<p style="color:#64748b;">Regenerando hashes bcrypt válidos para los usuarios demo...</p>

<?php
foreach ($usuarios as $username => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, activo = 1 WHERE username = ?");
    $stmt->execute([$hash, $username]);

    if ($stmt->rowCount() > 0) {
        echo "<div class='row'><span class='ok'>✓</span> <strong>$username</strong> → contraseña actualizada a <code>$password</code></div>";
    } else {
        // Intentar insertar si no existe
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt2->execute([$username]);
        if ($stmt2->fetchColumn() == 0) {
            echo "<div class='row'><span class='err'>✗</span> Usuario <strong>$username</strong> NO existe en la base de datos.</div>";
        } else {
            echo "<div class='row'><span class='ok'>✓</span> <strong>$username</strong> ya tenía la misma contraseña.</div>";
        }
    }
}
?>

<a href="/login" class="btn">→ Ir al Login</a>

<div class="warn">
<strong>⚠️ Importante:</strong> Por seguridad, elimina este archivo después de usarlo:<br>
<code>public/reset-passwords.php</code>
</div>
</div>
</body>
</html>
