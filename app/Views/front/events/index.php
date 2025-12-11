<div class="container-xl py-4">
    <h2 class="mb-3">Calendrier des évènements</h2>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div id="calendarFront"></div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cal = new FullCalendar.Calendar(document.getElementById('calendarFront'), {
            initialView: 'dayGridMonth',
            initialDate: '<?= esc($initialDate ?? date('Y-m-d')) ?>',
            locale: 'fr',
            timeZone: 'local',
            firstDay: 1,
            height: 'auto',
            headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listWeek' },
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            dayHeaderFormat: { weekday: 'short' },
            events: '<?= site_url('events/list') ?>',
            editable: false, selectable: false
        });
        cal.render();
    });
</script>
