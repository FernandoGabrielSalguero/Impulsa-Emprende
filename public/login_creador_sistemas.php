<?php
declare(strict_types=1);

session_start();

$loginError = $_GET['login_error'] ?? '';
$loginMessage = '';

if ($loginError === 'invalid') {
    $loginMessage = 'Correo o contrasena incorrectos.';
} elseif ($loginError === 'empty') {
    $loginMessage = 'Completa correo y contrasena para ingresar.';
}

if (isset($_SESSION['user_id'], $_SESSION['rol'])) {
    require_once __DIR__ . '/login_creador_sistemasModel.php';
    $redirectPath = resolverRutaPorRol((string) $_SESSION['rol']);
    header('Location: ' . $redirectPath);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de seguimiento</title>
    <style>
        @font-face {
            font-family: 'Montserrat';
            src: url('../assets/institucionales/fonts/Montserrat/Montserrat-VariableFont_wght.ttf') format('truetype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('../assets/institucionales/fonts/Montserrat/Montserrat-Italic-VariableFont_wght.ttf') format('truetype');
            font-weight: 100 900;
            font-style: italic;
            font-display: swap;
        }

        :root {
            color-scheme: light;
            --bg: #07111f;
            --bg-secondary: #0d1b31;
            --card: rgba(10, 20, 36, 0.88);
            --card-border: rgba(125, 211, 252, 0.18);
            --text: #f8fbff;
            --muted: #a9bad0;
            --accent: #7dd3fc;
            --accent-strong: #38bdf8;
            --danger-bg: rgba(239, 68, 68, 0.12);
            --danger-border: rgba(248, 113, 113, 0.35);
            --danger-text: #fecaca;
            --input-bg: rgba(255, 255, 255, 0.04);
            --input-border: rgba(255, 255, 255, 0.1);
            --shadow: 0 30px 80px rgba(3, 8, 18, 0.45);
            --radius: 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.22), transparent 34%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.18), transparent 28%),
                linear-gradient(145deg, var(--bg) 0%, var(--bg-secondary) 100%);
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(8px);
            z-index: 0;
        }

        body::before {
            width: 280px;
            height: 280px;
            top: 8%;
            right: 8%;
            background: rgba(56, 189, 248, 0.18);
        }

        body::after {
            width: 220px;
            height: 220px;
            bottom: 10%;
            left: 10%;
            background: rgba(14, 165, 233, 0.12);
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: min(100%, 430px);
        }

        .login-card {
            padding: 36px 30px 30px;
            border-radius: var(--radius);
            border: 1px solid var(--card-border);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.01)),
                var(--card);
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
        }

        .brand {
            display: grid;
            justify-items: center;
            gap: 14px;
            margin-bottom: 28px;
            text-align: center;
        }

        .brand img {
            width: min(240px, 70%);
            height: auto;
            object-fit: contain;
        }

        .brand h1 {
            margin: 0;
            font-size: clamp(1.6rem, 2vw, 2rem);
            line-height: 1.05;
        }

        .brand p {
            margin: 0;
            color: var(--muted);
            font-size: 0.96rem;
            line-height: 1.55;
        }

        .alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--danger-border);
            background: var(--danger-bg);
            color: var(--danger-text);
            font-size: 0.92rem;
        }

        .field {
            display: grid;
            gap: 8px;
            margin-bottom: 16px;
        }

        .field label {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .field input {
            width: 100%;
            border: 1px solid var(--input-border);
            border-radius: 16px;
            padding: 14px 16px;
            font: inherit;
            color: var(--text);
            background: var(--input-bg);
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .field input::placeholder {
            color: #7f92ac;
        }

        .field input:focus {
            border-color: rgba(125, 211, 252, 0.72);
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.12);
            transform: translateY(-1px);
        }

        .submit-btn {
            width: 100%;
            border: 0;
            border-radius: 16px;
            padding: 15px 18px;
            font: inherit;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            color: #082032;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-strong) 100%);
            box-shadow: 0 18px 35px rgba(14, 165, 233, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 42px rgba(14, 165, 233, 0.28);
        }

        .helper {
            margin: 16px 0 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.84rem;
            line-height: 1.5;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 22px 24px;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="login-card">
            <div class="brand">
                <img src="../assets/institucionales/Impulsa Desarrollo Blanco.png" alt="Impulsa desarrollo">
                <div>
                    <h1>Acceso al sistema</h1>
                    <p>Ingresa con las credenciales que te entregamos para acceder a tu espacio.</p>
                </div>
            </div>

            <?php if ($loginMessage !== ''): ?>
                <div class="alert"><?= htmlspecialchars($loginMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="/public/login_creador_sistemasController.php" method="POST" novalidate>
                <div class="field">
                    <label for="correo">Correo</label>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="tu@correo.com"
                        value="<?= htmlspecialchars((string) ($_GET['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="field">
                    <label for="contrasena">Contrasena</label>
                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        placeholder="Ingresa tu contrasena"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button class="submit-btn" type="submit">Iniciar sesion</button>
            </form>

            <p class="helper">No hay registro publico habilitado. Si necesitas acceso, te enviamos el usuario desde administracion.</p>
        </section>
    </main>
</body>
</html>
