<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\CoursModel;

class Dashboard extends BaseController
{
    protected $title = 'Dashboard';

    public function getIndex(): string
    {
        // Events à venir (prochains 60 jours), préchargés (pas d’AJAX)
        $from = date('Y-m-d 00:00:00');
        $to   = date('Y-m-d 23:59:59', strtotime('+60 days'));

        $eventsRows = (new EventModel())
            ->where('starts_at >=', $from)
            ->where('starts_at <=', $to)
            ->orderBy('starts_at','ASC')->findAll();

        $events = array_map(static function(array $e){
            return [
                'id'    => (int)$e['id'],
                'title' => (string)$e['title'],
                'start' => $e['starts_at'],
                'end'   => $e['ends_at'],
                'allDay'=> (bool)($e['all_day'] ?? 0),
                'color' => $e['color'] ?? null,
            ];
        }, $eventsRows);

        // Derniers cours publiés (6)
        $cours = (new CoursModel())
            ->where('created_at IS NOT NULL')
            ->orderBy('created_at','ASC')
            ->limit(6)->find();

        $stats = [
            'cours'    => (new CoursModel())->where('created_at IS NOT NULL')->countAllResults(),
            'upcoming' => count($eventsRows),
        ];

        return $this->view('/front/dashboard/index.php', [
            'events'      => $events,
            'initialDate' => date('Y-m-d'),
            'cours'       => $cours,
            'stats'       => $stats,
        ], ['saveData'=>true]);
    }
}
