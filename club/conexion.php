<?php
$host = 'localhost';
$dbname = 'grobe_club';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la base de dades no est activa o no s'ha importat, mostrem una pantalla amigable de John's Grobe
    ?>
    <!DOCTYPE html>
    <html lang="ca">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Grobe Club — Connexió Base de Dades</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,700&family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
            body {
                background: #0d0e12;
                color: #e2e8f0;
                font-family: 'Nunito Sans', sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
                box-sizing: border-box;
            }
            .card {
                background: rgba(22, 25, 32, 0.95);
                border: 1px solid rgba(210, 31, 60, 0.4);
                box-shadow: 0 20px 50px rgba(0,0,0,0.8), 0 0 30px rgba(210, 31, 60, 0.15);
                border-radius: 24px;
                padding: 36px;
                max-width: 540px;
                width: 100%;
                text-align: center;
            }
            h1 { font-family: 'Lora', serif; color: #fff; margin-top: 10px; margin-bottom: 8px; font-size: 1.8rem; }
            .badge { display: inline-block; background: rgba(210,31,60,0.15); color: #FF7B90; border: 1px solid rgba(210,31,60,0.3); padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
            p { font-size: 14px; color: #94a3b8; line-height: 1.6; margin: 16px 0; }
            .steps { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 18px; text-align: left; font-size: 13px; color: #cbd5e1; margin-bottom: 24px; }
            .steps ol { margin: 0; padding-left: 20px; }
            .steps li { margin-bottom: 8px; }
            .btn { display: inline-block; background: linear-gradient(135deg, #D21F3C 0%, #A81930 100%); color: #fff; text-decoration: none; font-weight: 800; padding: 12px 24px; border-radius: 12px; font-size: 13px; transition: transform 0.2s; }
            .btn:hover { transform: translateY(-2px); }
            .btn-outline { background: transparent; border: 1px solid rgba(255,255,255,0.15); color: #94a3b8; margin-left: 10px; }
            .error-raw { font-family: monospace; font-size: 11px; color: #f87171; background: rgba(239,68,68,0.1); padding: 8px; border-radius: 8px; margin-top: 15px; word-break: break-all; }
        </style>
    </head>
    <body>
        <div class="card">
            <span class="badge">Estat de la Base de Dades</span>
            <h1>Configuració Grobe Club</h1>
            <p>El servei de MySQL no està actiu o encara no s'ha importat la base de dades local.</p>
            <div class="steps">
                <strong>Passos per activar el Club:</strong>
                <ol>
                    <li>Obre el <strong>XAMPP Control Panel</strong> i inicia <strong>MySQL</strong> i <strong>Apache</strong>.</li>
                    <li>Obre phpMyAdmin (<code>http://localhost/phpmyadmin</code>).</li>
                    <li>Importa l'arxiu <code>database/grobe_club.sql</code> inclòs al projecte.</li>
                </ol>
            </div>
            <div>
                <a href="index.php" class="btn">🔄 Reintentar Connexió</a>
                <a href="../index.html" class="btn btn-outline">← Tornar a la Web</a>
            </div>
            <div class="error-raw">Detall tècnic: <?php echo htmlspecialchars($e->getMessage()); ?></div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
