<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EventModel;

class Events extends BaseController
{
    public function getIndex()
    {
        return $this->view('/front/events/index.php', [
            'initialDate' => date('Y-m-d'),
        ], ['saveData' => true]);
    }

    /** JSON public pour FullCalendar front: /events/list */
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
                ],
            ];
        }, $rows);

        return $this->response->setJSON($events);
    }
}
