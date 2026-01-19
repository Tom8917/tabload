<div class="container-xl py-4">
    <h2 class="mb-3">Tableau de bord</h2>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-auto">
                <div class="card-body">
                    <h5 class="mb-2">Calendrier des évènements à venir</h5>
                    <div id="frontCalendar"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-auto">
                <div class="card-body">
                    <h5 class="mb-3">Statistiques</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Cours publiés <span class="badge bg-primary rounded-pill"><?= esc($stats['cours'] ?? 0) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Évènements à venir <span class="badge bg-warning rounded-pill text-dark"><?= esc($stats['upcoming'] ?? 0) ?></span>
                        </li>
                    </ul>
                </div>
                </div>
            <div class="card shadow-sm border-0 mt-3 h-auto">
                <div class="card-body">
                    <h5 class="mb-3">Derniers cours</h5>
                    <div class="row g-3">
                        <?php foreach ($cours as $c): ?>
                            <div class="col-12">
                                <div class="d-flex gap-3 align-items-center">
                                    <img src="<?= esc($c['image'] ?: base_url('assets/img/placeholder-4x3.webp')) ?>"
                                         class="rounded" style="width:96px;height:72px;object-fit:cover" alt="">
                                    <div class="flex-grow-1">
                                        <a class="fw-semibold text-decoration-none" href="<?= site_url('cours/show/'.$c['slug']) ?>"><?= esc($c['title']) ?></a>
                                        <div class="small text-muted text-truncate"><?= esc($c['description'] ?? '') ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($cours)): ?>
                            <div class="text-muted small">Aucun cours publié pour le moment.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cal = new FullCalendar.Calendar(document.getElementById('frontCalendar'), {
            initialView: 'dayGridMonth',
            initialDate: '<?= esc($initialDate ?? date('Y-m-d')) ?>',
            locale: 'fr',
            firstDay: 1,
            height: 'auto',
            headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listWeek' },
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            dayHeaderFormat: { weekday: 'short' },
            events: '<?= site_url('events/list') ?>',   // <— JSON front
            editable: false, selectable: false
        });
        cal.render();
    });
</script>

