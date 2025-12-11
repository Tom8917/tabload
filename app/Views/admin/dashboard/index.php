<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>


<!--<style>-->
<!---->
<!--    .bandeau{-->
<!--        overflow:hidden;-->
<!--        white-space:nowrap;-->
<!--    }-->
<!---->
<!--    .bandeau p{-->
<!--        display:inline-block;-->
<!--        padding:0.2rem 0.5rem;-->
<!--        animation: defilement 12s linear infinite;-->
<!--        font-weight:600;-->
<!--    }-->
<!---->
<!--    @keyframes defilement{-->
<!--        0%   { transform:translateX(250%); }-->
<!--        100% { transform:translateX(-100%); }-->
<!--    }-->
<!--</style>-->
<!---->
<!--<body>-->
<!---->
<!--<div class="bandeau">-->
<!--    <p>✨ Bienvenue sur vapoT ! Profitez de nos nouveautés → Créez votre propre recette 🎉</p>-->
<!--</div>-->

<body>
<div class="container mt-2">
    <!-- Header -->
    <header class="mb-4">
        <h1 class="text-center">Page d'Accueil</h1>
        <p class="text-center">Bienvenue dans le tableau de bord</p>
    </header>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- FontAwesome JS -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<!-- CoreUI -->
<script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.5.0/dist/js/coreui.bundle.min.js"></script>



    <div class="container-xl py-4">

        <?php if(session('message')): ?>
            <div class="alert alert-success"><?= esc(session('message')) ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <h2 class="mb-3">Dashboard</h2>

        <!-- Stats -->
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="display-6 me-3">👤</div>
                        <div><div class="text-muted small">Users</div><div class="h4 m-0"><?= esc($stats['users']) ?></div></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="display-6 me-3">📝</div>
                        <div><div class="text-muted small">Tasks</div><div class="h4 m-0"><?= esc($stats['tasks']) ?></div></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="display-6 me-3">📄</div>
                        <div><div class="text-muted small">Pages</div><div class="h4 m-0"><?= esc($stats['pages']) ?></div></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="display-6 me-3">📚</div>
                        <div><div class="text-muted small">Cours</div><div class="h4 m-0"><?= esc($stats['cours']) ?></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendrier + Graph -->
        <div class="row g-3 mt-2">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="m-0">Calendrier des évènements</h5>
                            <small class="text-muted">Clique sur un jour pour créer</small>
                        </div>
                        <div id="calendarAdmin"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="mb-3">Activité hebdo (exemple)</h5>
                        <canvas id="chartCours"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar + Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function(){
            const initialDate = '<?= esc($initialDate ?? date('Y-m-d')) ?>';

            // éléments DOM
            const elCal      = document.getElementById('calendarAdmin');
            const modalEl    = document.getElementById('modalEvent');
            const form       = document.getElementById('eventForm');
            const delBtn     = document.getElementById('ev_delete_btn');
            const hTitle     = document.getElementById('eventModalTitle');

            // blocs répétition
            const blockSingle = document.getElementById('block_single');
            const blockRepeat = document.getElementById('block_repeat');
            const cbRepeat    = document.getElementById('ev_repeat');

            // champs du formulaire
            const f = {
                id:        document.getElementById('ev_id'),
                title:     document.getElementById('ev_title'),
                starts:    document.getElementById('ev_starts_at'),
                ends:      document.getElementById('ev_ends_at'),
                type:      document.getElementById('ev_type'),
                color:     document.getElementById('ev_color'),
                location:  document.getElementById('ev_location'),
                notes:     document.getElementById('ev_notes'),
                allDay:    document.getElementById('ev_all_day'),
                dateFrom:  document.getElementById('ev_date_from'),
                dateTo:    document.getElementById('ev_date_to'),
                t1s:       document.getElementById('ev_time1_start'),
                t1e:       document.getElementById('ev_time1_end'),
                t2s:       document.getElementById('ev_time2_start'),
                t2e:       document.getElementById('ev_time2_end'),
            };

            // Si le modal n'est pas présent, on ne bloque pas l’affichage du calendrier
            let modal = null;
            if (modalEl) modal = new bootstrap.Modal(modalEl);

            function toLocalInput(dtStr){
                if (!dtStr) return '';
                return dtStr.replace(' ', 'T').slice(0,16); // 'YYYY-MM-DDTHH:MM'
            }

            // CREATION
            window.openCreate = function(dateStr=null){
                if (!modal) return; // évite erreur si pas de modal
                hTitle.textContent = 'Nouvel évènement';
                form.action = '<?= site_url('admin/events/store') ?>';
                f.id.value = ''; f.title.value=''; f.type.value=''; f.color.value='#6c5ce7';
                f.location.value=''; f.notes.value='';
                f.starts.value = dateStr ? dateStr+'T09:00' : '';
                f.ends.value   = dateStr ? dateStr+'T17:00' : '';
                f.allDay.checked = false;
                delBtn.classList.add('d-none');

                // répétition
                if (cbRepeat && blockRepeat && blockSingle) {
                    cbRepeat.checked = !!dateStr;
                    blockRepeat.style.display = cbRepeat.checked ? '' : 'none';
                    blockSingle.style.display = cbRepeat.checked ? 'none' : '';
                    if (dateStr) { f.dateFrom.value = dateStr; f.dateTo.value = dateStr; }
                }

                modal.show();
            };

            // EDITION
            function openEdit(ev){
                if (!modal) return;
                hTitle.textContent = 'Modifier l’évènement';
                form.action = '<?= site_url('admin/events/update') ?>' + '/' + ev.id;
                f.id.value = ev.id ?? '';
                f.title.value = ev.title ?? '';
                f.starts.value = toLocalInput(ev.startStr);
                f.ends.value   = toLocalInput(ev.endStr || ev.startStr);
                f.type.value = ev.extendedProps?.type ?? '';
                f.color.value = ev.backgroundColor || ev.borderColor || ev.color || '#6c5ce7';
                f.location.value = ev.extendedProps?.location ?? '';
                f.notes.value = ev.extendedProps?.notes ?? '';
                f.allDay.checked = !!ev.allDay;

                // mode simple en édition
                if (cbRepeat && blockRepeat && blockSingle) {
                    cbRepeat.checked = false;
                    blockRepeat.style.display = 'none';
                    blockSingle.style.display = '';
                }

                delBtn.href = '<?= site_url('admin/events/delete') ?>' + '/' + ev.id;
                delBtn.classList.remove('d-none');
                modal.show();
            }

            // Calendar
            const cal = new FullCalendar.Calendar(elCal, {
                initialView: 'dayGridMonth',
                initialDate: initialDate,
                locale: 'fr',
                timeZone: 'local',
                firstDay: 1,
                height: 'auto',
                headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listWeek' },
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                dayHeaderFormat: { weekday: 'short' },
                events: '<?= site_url('admin/events/list') ?>',
                dateClick(info){ openCreate(info.dateStr); },
                eventClick(info){ openEdit(info.event); }
            });
            cal.render();

            // toggle répétition
            if (cbRepeat) {
                cbRepeat.addEventListener('change', () => {
                    const on = cbRepeat.checked;
                    if (blockRepeat && blockSingle) {
                        blockRepeat.style.display = on ? '' : 'none';
                        blockSingle.style.display = on ? 'none' : '';
                    }
                });
            }
        })();
    </script>
</body>
</html>
