<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body>
<div class="container mt-2">
    <!-- Header -->
    <header class="mb-4">
        <h1 class="mb-3 text-center">Tableau de bord</h1>
    </header>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- FontAwesome JS -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- CoreUI -->
    <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.5.0/dist/js/coreui.bundle.min.js"></script>


    <div class="container-xl py-4">

        <?php if (session('message')): ?>
            <div class="alert alert-success"><?= esc(session('message')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="display-6 me-3"><i class="fa-regular fa-user"></i></div>
                        <div>
                            <div class="text-muted small">Nombre d'utilisateur</div>
                            <div class="h4 m-0"><?= esc($stats['users']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="display-6 me-3"><i class="fa-regular fa-file"></i></div>
                        <div>
                            <div class="text-muted small">Nombre de bilans</div>
                            <div class="h4 m-0"><?= esc($stats['reports']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xl py-4">
        <div class="row g-3 g-lg-4">

            <!-- Créer un bilan -->
            <div class="col-12 col-md-6 col-xl-3">
                <a href="<?= base_url('admin/reports/new') ?>" class="tdb-card tdb-primary">
                    <div class="tdb-icon"><i class="fa-solid fa-plus"></i></div>
                    <div class="tdb-title">Créer un bilan</div>
                    <div class="tdb-desc">Démarre un nouveau document avec tes sections, notes et tableaux.</div>
                    <div class="tdb-cta">
                        <span>Commencer</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
            </div>

            <!-- Mes bilans -->
            <div class="col-12 col-md-6 col-xl-3">
                <a href="<?= base_url('admin/reports') ?>" class="tdb-card">
                    <div class="tdb-icon"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="tdb-title">Les bilans</div>
                    <div class="tdb-desc">Retrouve, édite et exporte les bilans existants.</div>
                    <div class="tdb-cta">
                        <span>Ouvrir</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
            </div>

            <!-- TabLoad -->
            <div class="col-12 col-md-6 col-xl-3">
                <a href="<?= base_url('admin/tabload') ?>" class="tdb-card">
                    <div class="tdb-icon"><i class="fa-solid fa-table"></i></div>
                    <div class="tdb-title">TabLoad</div>
                    <div class="tdb-desc">Transforme un tableau brut en tableau propre et exportable.</div>
                    <div class="tdb-cta">
                        <span>Utiliser</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
            </div>

            <!-- Médiathèque -->
            <div class="col-12 col-md-6 col-xl-3">
                <a href="<?= base_url('admin/media') ?>" class="tdb-card">
                    <div class="tdb-icon"><i class="fa-regular fa-image"></i></div>
                    <div class="tdb-title">Médiathèque</div>
                    <div class="tdb-desc">Importe tes images, organise-les, et réutilise-les dans tes bilans.</div>
                    <div class="tdb-cta">
                        <span>Parcourir</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
            </div>

            <!-- Logs -->
            <div class="col-12 col-md-6 col-xl-3">
                <a href="<?= base_url('admin/logs') ?>" class="tdb-card">
                    <div class="tdb-icon"><i class="fa-solid fa-list-ol"></i></div>
                    <div class="tdb-title">Logs</div>
                    <div class="tdb-desc">Observer les logs.</div>
                    <div class="tdb-cta">
                        <span>Parcourir</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
            </div>

        </div>
    </div>

    <style>
        /* ===========================
           Dashboard cards (front)
           Light + Dark (CoreUI friendly)
           =========================== */

        /* Light (par défaut) */
        .tdb-card{
            display:block;
            text-decoration:none;
            color: inherit;

            border: 1px solid rgba(0,0,0,.08);
            border-radius: 16px;
            padding: 18px;
            height: 100%;

            background: rgba(255,255,255,.78);
            transition: transform .15s ease, border-color .15s ease, background .15s ease;
        }

        .tdb-card:hover{
            transform: translateY(-2px);
            border-color: rgba(0,0,0,.15);
            background: rgba(255,255,255,.95);
        }

        .tdb-primary{
            border-color: rgba(13,110,253,.25);
            background: rgba(13,110,253,.06);
        }
        .tdb-primary:hover{
            border-color: rgba(13,110,253,.38);
            background: rgba(13,110,253,.09);
        }

        .tdb-icon{
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display:flex;
            align-items:center;
            justify-content:center;

            border: 1px solid rgba(0,0,0,.08);
            background: rgba(0,0,0,.03);
            margin-bottom: 12px;
            font-size: 18px;
        }

        .tdb-title{
            font-weight: 700;
            margin-bottom: 6px;
        }

        .tdb-desc{
            color: rgba(0,0,0,.60);
            font-size: .93rem;
            line-height: 1.35;
            margin-bottom: 14px;
            min-height: 40px;
        }

        .tdb-cta{
            display:flex;
            align-items:center;
            justify-content: space-between;
            font-weight: 600;
            color: rgba(0,0,0,.75);
        }
        .tdb-cta i{ opacity: .6; }

        /* Focus clavier */
        .tdb-card:focus-visible{
            outline: 3px solid rgba(13,110,253,.35);
            outline-offset: 2px;
        }

        /* ======================================
           DARK : CoreUI (data-coreui-theme="dark")
           ====================================== */
        html[data-coreui-theme="dark"] .tdb-card{
            border-color: rgba(255,255,255,.10);
            background: rgba(20, 24, 28, .55);   /* effet "glass" dark */
        }

        html[data-coreui-theme="dark"] .tdb-card:hover{
            border-color: rgba(255,255,255,.18);
            background: rgba(28, 33, 39, .75);
        }

        html[data-coreui-theme="dark"] .tdb-primary{
            border-color: rgba(13,110,253,.35);
            background: rgba(13,110,253,.12);
        }
        html[data-coreui-theme="dark"] .tdb-primary:hover{
            border-color: rgba(13,110,253,.50);
            background: rgba(13,110,253,.16);
        }

        html[data-coreui-theme="dark"] .tdb-icon{
            border-color: rgba(255,255,255,.10);
            background: rgba(255,255,255,.06);
        }

        html[data-coreui-theme="dark"] .tdb-desc{
            color: rgba(255,255,255,.70);
        }

        html[data-coreui-theme="dark"] .tdb-cta{
            color: rgba(255,255,255,.80);
        }

        /* ======================================
           Fallback : si pas CoreUI, suivre l'OS
           ====================================== */
        @media (prefers-color-scheme: dark){
            html:not([data-coreui-theme]) .tdb-card{
                border-color: rgba(255,255,255,.10);
                background: rgba(20, 24, 28, .55);
            }
            html:not([data-coreui-theme]) .tdb-card:hover{
                border-color: rgba(255,255,255,.18);
                background: rgba(28, 33, 39, .75);
            }
            html:not([data-coreui-theme]) .tdb-primary{
                border-color: rgba(13,110,253,.35);
                background: rgba(13,110,253,.12);
            }
            html:not([data-coreui-theme]) .tdb-primary:hover{
                border-color: rgba(13,110,253,.50);
                background: rgba(13,110,253,.16);
            }
            html:not([data-coreui-theme]) .tdb-icon{
                border-color: rgba(255,255,255,.10);
                background: rgba(255,255,255,.06);
            }
            html:not([data-coreui-theme]) .tdb-desc{
                color: rgba(255,255,255,.70);
            }
            html:not([data-coreui-theme]) .tdb-cta{
                color: rgba(255,255,255,.80);
            }
        }
    </style>
</body>
</html>

</div>