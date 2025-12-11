<!DOCTYPE html>
<html lang="fr-FR" data-coreui-theme="auto">
<head>
    <base href="<?= base_url('./') ?>">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

    <!-- Bootstrap/CoreUI & Vendor CSS habituels -->
    <link rel="stylesheet" href="<?= base_url('/vendors/simplebar/css/simplebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/vendors/simplebar.css') ?>">
    <link href="<?= base_url('/css/style.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/css/toastr.min.css') ?>">

    <!-- Fallback Violet Premium (supprime si déjà défini globalement) -->
    <style>
        :root {
            --violet-950:#12081f;
            --violet-900:#1d0e2f;
            --violet-800:#2a1743;
            --violet-700:#3a225d;
            --violet-600:#51317d;
            --violet-500:#6a49a1;
            --violet-400:#8b6bc0;
            --violet-300:#b19ade;
            --violet-200:#d6c9f0;

            --yellow:#d7fa00;

            --line-500:#b7a9d9;
            --line-300:#e6def7;

            --r-sm:10px;
            --r-md:14px;
            --r-lg:18px;
            --shadow-1:0 6px 18px rgba(0,0,0,.10);
            --shadow-2:0 10px 28px rgba(0,0,0,.16);
        }

        /* ===== Fond selon thème ===== */
        [data-coreui-theme="light"] body.login-page {
            background: radial-gradient(1200px 600px at 20% -10%, #fff 0%, #ffffff 35%, var(--violet-200) 100%);
            background-color: var(--violet-200);
            color: inherit;
        }

        [data-coreui-theme="dark"] body.login-page {
            background: radial-gradient(1100px 600px at 80% -20%, var(--violet-900) 0%, var(--violet-900) 45%, var(--violet-700) 100%);
            background-color: var(--violet-900);
            color: inherit;
        }

        /* ===== Carte login (transparente + encadré dégradé Violet) ===== */
        .login-card.panel-border-grad {
            padding: 1px;
            border-radius: var(--r-lg);
            background: linear-gradient(135deg, var(--violet-600), var(--violet-500));
            box-shadow: var(--shadow-2);
            max-width: 420px;
            width: 100%;
        }
        .login-card .inner {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(6px);
            border-radius: calc(var(--r-lg) - 1px);
            padding: 24px 22px;
        }
        [data-coreui-theme="dark"] .login-card .inner {
            background: rgba(29, 14, 47, 0.35);
            backdrop-filter: blur(6px);
            color: inherit;
        }

        /* ===== Logo + titres ===== */
        .login-logo {
            display: block;
            margin: 0 auto 10px;
            max-width: 190px;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,.18));
        }
        .login-title {
            text-align: center;
            margin: 8px 0 2px;
            font-weight: 700;
        }
        .login-sub {
            text-align: center;
            margin-bottom: 18px;
            opacity: .8;
        }

        /* ===== Inputs & boutons ===== */
        .form-control {
            border-radius: 12px;
            border: 1px solid var(--line-500);
            background: rgba(255, 255, 255, .85);
        }
        [data-coreui-theme="dark"] .form-control {
            background: rgba(29, 14, 47, .35);
            color: inherit;
            border-color: color-mix(in oklab, var(--line-500) 70%, transparent);
        }
        .form-control:focus {
            border-color: var(--violet-600);
            box-shadow: 0 0 0 .2rem color-mix(in oklab, var(--violet-400) 28%, white);
        }

        /* Bouton violet principal */
        .btn-primary {
            background-color: var(--violet-600);
            border-color: color-mix(in oklab, var(--violet-600) 65%, black);
            color: #fff;
            border-radius: 12px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.20), var(--shadow-1);
        }
        .btn-primary:hover {
            background-color: var(--violet-500);
            border-color: color-mix(in oklab, var(--violet-500) 65%, black);
            filter: brightness(1.02);
        }
        .btn-primary:focus {
            box-shadow: var(--shadow-1), 0 0 0 .2rem color-mix(in oklab, var(--violet-400) 25%, white);
        }

        /* Bouton secondaire en jaune */
        .btn-secondary {
            background-color: var(--yellow);
            border-color: color-mix(in oklab, var(--yellow) 65%, black);
            color: #1d1d1d;
            border-radius: 12px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.20), var(--shadow-1);
        }
        .btn-secondary:hover {
            filter: brightness(1.05);
        }

        /* Liens */
        a, a:visited {
            color: inherit;
            text-decoration: none;
        }
        a:hover, a:focus {
            color: var(--violet-600);
            text-decoration: underline;
        }
        a:active {
            color: var(--violet-500);
        }
        .link-violet {
            text-decoration: none;
            box-shadow: inset 0 -2px 0 0 var(--violet-600);
            transition: box-shadow .2s ease, color .2s ease;
        }
        .link-violet:hover {
            box-shadow: inset 0 -3px 0 0 var(--violet-500);
            color: var(--violet-600);
        }

        /* Layout centré */
        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
    </style>

</head>

<body class="login-page">
<div class="login-wrap">
    <div class="login-card panel-border-grad">
        <div class="inner">
<!--            <img src="--><?php //= base_url('assets/brand/coda_logo.png') ?><!--" alt="coda-portfolio" class="login-logo">-->

            <h1 class="login-title">TABLOAD</h1>
            <h2 class="login-title">Connexion</h2>
            <p class="login-sub"></p>

            <?php if (isset($error)) { ?>
                <div class="alert alert-danger py-2 mb-3" role="alert">
                    <?= esc($error) ?>
                </div>
            <?php } ?>

            <form action="<?= base_url('/login'); ?>" method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input id="email" type="email" name="email" class="form-control" placeholder="adresse@mail.com"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input id="password" type="password" name="password" class="form-control" placeholder="********"
                           required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>

            <div class="text-center mt-3">
                <a href="<?= base_url('/login/register') ?>" class="link-emerald">Créer un compte</a><br>
                <a href="<?= base_url('/login/forgot') ?>" class="link-emerald small">Mot de passe oublié ?</a>
            </div>
        </div>
    </div>
</div>

<!-- JS habituels -->
<script src="<?= base_url('/js/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= base_url('/js/config.js') ?>"></script>
<script src="<?= base_url('/js/color-modes.js') ?>"></script>
<script src="<?= base_url('/vendors/@coreui/coreui/js/coreui.bundle.min.js') ?>"></script>
<script src="<?= base_url('/vendors/simplebar/js/simplebar.min.js') ?>"></script>
<script src="<?= base_url('/vendors/@coreui/utils/js/index.js') ?>"></script>
<script src="<?= base_url('/js/toastr.min.js') ?>"></script>

<!-- Toastr message erreur (optionnel) -->
<script>
    <?php if (isset($error)) { ?>
    toastr.error("<?= addslashes($error) ?>", "Erreur", {
        closeButton: true, progressBar: true, timeOut: 5000
    });
    <?php } ?>
</script>
</body>
</html>
