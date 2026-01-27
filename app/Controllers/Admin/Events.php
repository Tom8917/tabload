<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;

class Events extends BaseController
{
    protected $require_auth = true;

    public function getIndex()
    {
        return $this->view('/admin/events/index.php', [
            'initialDate' => date('Y-m-d'),
        ], ['saveData' => true]);
    }

    public function getList()
    {
        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end')   ?? date('Y-m-t');

        $rows = (new EventModel())
            ->where('starts_at >=', $start.' 00:00:00')
            ->where('starts_at <=', $end.' 23:59:59')
            ->orderBy('starts_at','ASC')
            ->findAll();

        $events = array_map(static function(array $e){
            return [
                'id'    => (int)$e['id'],
                'title' => (string)$e['title'],
                'start' => $e['starts_at'],
                'end'   => $e['ends_at'],
                'allDay'=> (bool)($e['all_day'] ?? 0),
                'color' => $e['color'] ?? null,
                'extendedProps' => [
                    'type'      => $e['type'] ?? null,
                    'location'  => $e['location'] ?? null,
                    'course_id' => $e['course_id'] ?? null,
                    'notes'     => $e['notes'] ?? null,
                ],
            ];
        }, $rows);

        return $this->response->setJSON($events);
    }

    public function postStore()
    {
        $repeat = (bool) $this->request->getPost('repeat_daily');

        if ($repeat) {
            $title   = (string) $this->request->getPost('title');
            $type    = (string) $this->request->getPost('type');
            $color   = $this->sanitizeColor($this->request->getPost('color') ?? null);
            $loc     = (string) $this->request->getPost('location');
            $notes   = (string) $this->request->getPost('notes');
            $allDay  = $this->request->getPost('all_day') ? 1 : 0;

            $dateFrom = (string) $this->request->getPost('date_from');
            $dateTo   = (string) $this->request->getPost('date_to');

            $t1s = (string) $this->request->getPost('time1_start');
            $t1e = (string) $this->request->getPost('time1_end');
            $t2s = (string) $this->request->getPost('time2_start');
            $t2e = (string) $this->request->getPost('time2_end');

            if (!$dateFrom || !$dateTo) {
                return redirect()->to(site_url('admin/events'))->with('error','Renseigne la période (du… au…).');
            }
            if (!$allDay && (!$t1s || !$t1e)) {
                return redirect()->to(site_url('admin/events'))->with('error','Renseigne au moins un créneau horaire.');
            }

            $dates = [];
            $start = new \DateTime($dateFrom.' 00:00:00');
            $end   = new \DateTime($dateTo.' 00:00:00');
            if ($end < $start) {
                return redirect()->to(site_url('admin/events'))->with('error','La date de fin doit être ≥ date de début.');
            }
            for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
                $dates[] = $d->format('Y-m-d');
            }

            $model = new EventModel();
            $inserted = 0;

            foreach ($dates as $d) {
                if ($allDay) {
                    $model->insert([
                        'title'     => $title,
                        'type'      => $type,
                        'starts_at' => $d.' 00:00:00',
                        'ends_at'   => $d.' 23:59:59',
                        'all_day'   => 1,
                        'location'  => $loc,
                        'notes'     => $notes,
                        'color'     => $color,
                    ]);
                    $inserted++;
                } else {
                    if ($t1s && $t1e) {
                        $model->insert([
                            'title'     => $title,
                            'type'      => $type,
                            'starts_at' => $d.' '.$t1s.':00',
                            'ends_at'   => $d.' '.$t1e.':00',
                            'all_day'   => 0,
                            'location'  => $loc,
                            'notes'     => $notes,
                            'color'     => $color,
                        ]);
                        $inserted++;
                    }
                    if ($t2s && $t2e) {
                        $model->insert([
                            'title'     => $title,
                            'type'      => $type,
                            'starts_at' => $d.' '.$t2s.':00',
                            'ends_at'   => $d.' '.$t2e.':00',
                            'all_day'   => 0,
                            'location'  => $loc,
                            'notes'     => $notes,
                            'color'     => $color,
                        ]);
                        $inserted++;
                    }
                }
            }

            if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => true, 'count' => $inserted]);
            return redirect()->to(site_url('admin/events'))->with('message', $inserted.' évènement(s) créé(s)');
        }

        $data = $this->request->getPost(['title','type','starts_at','ends_at','location','notes','color']);
        $data['all_day'] = $this->request->getPost('all_day') ? 1 : 0;
        $data['color']   = $this->sanitizeColor($data['color'] ?? null);

        if ($date = $this->request->getPost('date')) {
            $data['starts_at'] = $date.' 00:00:00';
            $data['ends_at']   = $date.' 23:59:59';
        } else {
            $data['starts_at'] = $this->normDT($data['starts_at'] ?? null);
            $data['ends_at']   = $this->normDT($data['ends_at'] ?? null);

            if ($data['all_day'] && $data['starts_at'] && strlen($data['starts_at']) === 10) $data['starts_at'] .= ' 00:00:00';
            if ($data['all_day'] && $data['ends_at']   && strlen($data['ends_at'])   === 10) $data['ends_at']   .= ' 23:59:59';
        }
        if ($data['starts_at'] && empty($data['ends_at'])) $data['ends_at'] = $data['starts_at'];

        $id = (new EventModel())->insert($data, true);
        if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => true, 'id' => $id]);
        return redirect()->to(site_url('admin/events'))->with('message','Évènement créé');
    }

    public function postUpdate(int $id)
    {
        $data = $this->request->getPost(['title','type','starts_at','ends_at','location','notes','color']);
        $data['all_day']   = $this->request->getPost('all_day') ? 1 : 0;
        $data['color']     = $this->sanitizeColor($data['color'] ?? null);
        $data['starts_at'] = $this->normDT($data['starts_at'] ?? null);
        $data['ends_at']   = $this->normDT($data['ends_at'] ?? null);

        if ($data['all_day']) {
            if ($data['starts_at'] && strlen($data['starts_at']) === 10) $data['starts_at'] .= ' 00:00:00';
            if ($data['ends_at']   && strlen($data['ends_at'])   === 10) $data['ends_at']   .= ' 23:59:59';
        }
        if ($data['starts_at'] && empty($data['ends_at'])) $data['ends_at'] = $data['starts_at'];

        (new EventModel())->update($id, $data);
        return redirect()->to(site_url('admin/events'))->with('message','Évènement mis à jour');
    }

    public function getDelete(int $id)
    {
        (new EventModel())->delete($id);
        return redirect()->to(site_url('admin/events'))->with('message','Évènement supprimé');
    }

    private function sanitizeColor(?string $hex): ?string
    {
        return ($hex && preg_match('/^#[0-9a-f]{6}$/i', $hex)) ? $hex : null;
    }
    private function normDT(?string $v): ?string
    {
        if (!$v) return null;
        $v = trim($v);
        if ($v === '') return null;
        if (str_contains($v, 'T')) $v = str_replace('T', ' ', $v);
        if (preg_match('#^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$#', $v)) $v .= ':00';
        return $v;
    }
}
