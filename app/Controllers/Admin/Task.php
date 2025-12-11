<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Task extends BaseController
{
    protected $require_auth = true;
    protected $requiredPermissions = ['administrateur'];

    public function getindex($id = null)
    {
        $tm = new \App\Models\TaskModel();

        if ($id == null) {
            $tasks = $tm->getAllTasks();

            return $this->view("/admin/task/index.php", [
                'tasks' => $tasks
            ], true);
        } else {
            $task = $tm->find($id);

            if ($task) {
                return $this->view("/admin/task/task.php", [
                    'task' => $task
                ], true);
            } else {
                return $this->view("/admin/task/task.php", [
                ], true);
            }
        }
    }

    public function postupdate() {
        $data = $this->request->getPost();
        $tm = Model("TaskModel");
        $data['limit_time'] = isset($data['limit_time']) ? date('Y-m-d', strtotime($data['limit_time'])) : null;
        $data['status'] = isset($data['status']) && $data['status'] === "Fait" ? "Fait" : "À faire";
        if (isset($data['limit_time']) && $data['limit_time'] == '1970-01-01') {
            $data['limit_time'] = null;
        } else {
            $data['limit_time'] = isset($data['limit_time']) ? date('Y-m-d', strtotime($data['limit_time'])) : null;
        }
        if ($tm->updateTask($data['id'], $data)) {
            $this->success("La tâche a bien été modifié");
        } else {
            $this->error("Une erreur est survenue");
        }
        $this->redirect("/admin/task");
    }

    public function postcreate() {
        $data = $this->request->getPost();
        $tm = Model("TaskModel");
        $data['limit_time'] = isset($data['limit_time']) ? date('Y-m-d', strtotime($data['limit_time'])) : null;
        $data['status'] = isset($data['status']) && $data['status'] === "Fait" ? "Fait" : "À faire";
        if (isset($data['limit_time']) && $data['limit_time'] == '1970-01-01') {
            $data['limit_time'] = null;
        } else {
            $data['limit_time'] = isset($data['limit_time']) ? date('Y-m-d', strtotime($data['limit_time'])) : null;
        }
        if ($tm->createTask($data)) {
            $this->success("La tâche a bien été ajouté.");
            $this->redirect("/admin/task");
        } else {
            $errors = $tm->errors();
            foreach ($errors as $error) {
                $this->error($error);
            }
            $this->redirect("/admin/task/new");
        }
    }

    public function getdelete($id) {
        $tm = Model('TaskModel');
        if ($tm->deleteTask($id)) {
            $this->success("Tâche supprimé");
        } else {
            $this->error("Tâche non supprimé");
        }
        $this->redirect('/admin/task');
    }


    public function getTasks()
    {
        $tm = Model("App\Models\TaskModel");
        $tasks = $tm->getAllTasks();

        return $this->response->setJSON($tasks);
    }

    public function postSearchTask()
    {
        $TaskModel = new \App\Models\TaskModel();

        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $searchValue = $this->request->getPost('search')['value'] ?? '';

        $orderColumnIndex = $this->request->getPost('order')[0]['column'];
        $orderDirection = $this->request->getPost('order')[0]['dir'];
        $orderColumnName = $this->request->getPost('columns')[$orderColumnIndex]['data'];

        $data = $TaskModel->getPaginatedTasks($start, $length, $searchValue, $orderColumnName, $orderDirection);
        $totalRecords = $TaskModel->getTotalTasks();
        $filteredRecords = $TaskModel->getFilteredTasks($searchValue);

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }
}
