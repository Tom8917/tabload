<div class="container-xl py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Gestion des évènements</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEvent" onclick="openCreate()">Créer</button>
    </div>

    <?php if(session('message')): ?>
        <div class="alert alert-success"><?= esc(session('message')) ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div id="calendarAdmin"></div>
        </div>
    </div>
</div>

<!-- Modal création/édition (identique utilisé aussi dans le Dashboard admin) -->
<div class="modal fade" id="modalEvent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="eventForm" class="modal-content" method="post" action="<?= site_url('admin/events/store') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="ev_id">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Nouvel évènement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Titre</label>
                    <input name="title" id="ev_title" class="form-control" required>
                </div>

                <!-- Mode simple -->
                <div id="block_single">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Début</label>
                            <input type="datetime-local" lang="fr" name="starts_at" id="ev_starts_at" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Fin</label>
                            <input type="datetime-local" lang="fr" name="ends_at" id="ev_ends_at" class="form-control">
                        </div>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="ev_all_day" name="all_day" value="1">
                        <label class="form-check-label" for="ev_all_day">Toute la journée</label>
                    </div>
                </div>

                <hr class="my-3">

                <!-- Répétition quotidienne -->
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="ev_repeat" name="repeat_daily" value="1">
                    <label class="form-check-label" for="ev_repeat">Répéter chaque jour (période + créneaux)</label>
                </div>

                <div id="block_repeat" class="border rounded p-3" style="display:none;">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Du</label>
                            <input type="date" lang="fr" name="date_from" id="ev_date_from" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Au</label>
                            <input type="date" lang="fr" name="date_to" id="ev_date_to" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-sm-6">
                            <label class="form-label">Créneau 1 - début</label>
                            <input type="time" lang="fr" name="time1_start" id="ev_time1_start" class="form-control" value="09:00">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Créneau 1 - fin</label>
                            <input type="time" lang="fr" name="time1_end" id="ev_time1_end" class="form-control" value="17:00">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-sm-6">
                            <label class="form-label">Créneau 2 - début (optionnel)</label>
                            <input type="time" lang="fr" name="time2_start" id="ev_time2_start" class="form-control" placeholder="13:00">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Créneau 2 - fin (optionnel)</label>
                            <input type="time" lang="fr" name="time2_end" id="ev_time2_end" class="form-control" placeholder="17:00">
                        </div>
                    </div>

                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="ev_all_day_repeat" value="1" onclick="
              const on = this.checked;
              ev_time1_start.disabled = on; ev_time1_end.disabled = on;
              ev_time2_start.disabled = on; ev_time2_end.disabled = on;
              ev_all_day.checked = on;">
                        <label class="form-check-label" for="ev_all_day_repeat">Toute la journée (chaque jour)</label>
                    </div>
                    <small class="text-muted d-block mt-2">Ex: 9–12 <em>et</em> 13–17 => renseigne 2 créneaux.</small>
                </div>

                <hr class="my-3">

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label">Type</label>
                        <input name="type" id="ev_type" class="form-control" placeholder="cours, examen…">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Couleur</label>
                        <input type="color" name="color" id="ev_color" class="form-control form-control-color" value="#6c5ce7">
                    </div>
                </div>
                <div class="mt-2">
                    <label class="form-label">Lieu</label>
                    <input name="location" id="ev_location" class="form-control">
                </div>
                <div class="mt-2">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" id="ev_notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <a id="ev_delete_btn" class="btn btn-outline-danger d-none">Supprimer</a>
                <button class="btn btn-primary" type="submit">Enregistrer</button>
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Fermer</button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    (function(){
        const initialDate = '<?= esc($initialDate ?? date('Y-m-d')) ?>';
        const elCal = document.getElementById('calendarAdmin');
        const modalEl = document.getElementById('modalEvent');
        const modal = new bootstrap.Modal(modalEl);
        const form  = document.getElementById('eventForm');
        const delBtn= document.getElementById('ev_delete_btn');
        const hTitle= document.getElementById('eventModalTitle');

        const f = {
            id: ev_id, title: ev_title, starts: ev_starts_at, ends: ev_ends_at,
            type: ev_type, color: ev_color, location: ev_location, notes: ev_notes, allDay: ev_all_day
        };

        function toLocalInput(dtStr){ if(!dtStr) return ''; return dtStr.replace(' ', 'T').slice(0,16); }

        window.openCreate = function(dateStr=null){
            hTitle.textContent = 'Nouvel évènement';
            form.action = '<?= site_url('admin/events/store') ?>';
            f.id.value = ''; f.title.value=''; f.type.value=''; f.color.value='#6c5ce7';
            f.location.value=''; f.notes.value='';
            f.starts.value = dateStr ? dateStr+'T09:00' : '';
            f.ends.value   = dateStr ? dateStr+'T17:00' : '';
            f.allDay.checked = false;
            delBtn.classList.add('d-none');

            // Préremplir bloc répétition
            ev_repeat.checked = !!dateStr;
            block_repeat.style.display = ev_repeat.checked ? '' : 'none';
            block_single.style.display = ev_repeat.checked ? 'none' : '';
            if (dateStr) { ev_date_from.value = dateStr; ev_date_to.value = dateStr; }

            modal.show();
        };

        function openEdit(ev){
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

            // Forcer mode simple en édition d'une occurrence
            ev_repeat.checked = false;
            block_repeat.style.display = 'none';
            block_single.style.display = '';

            delBtn.href = '<?= site_url('admin/events/delete') ?>' + '/' + ev.id;
            delBtn.classList.remove('d-none');
            modal.show();
        }

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

        // Toggle repeat blocks
        ev_repeat.addEventListener('change', () => {
            const on = ev_repeat.checked;
            block_repeat.style.display = on ? '' : 'none';
            block_single.style.display = on ? 'none' : '';
        });
    })();
</script>
