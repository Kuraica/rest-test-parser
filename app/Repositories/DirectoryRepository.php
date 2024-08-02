<?php

namespace App\Repositories;

use App\Models\Directory;
use App\Repositories\Contracts\DirectoryRepositoryInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;

class DirectoryRepository implements DirectoryRepositoryInterface
{
    public function __construct(
        protected Directory $model,
        private readonly DatabaseManager $db
    ) {}

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function firstOrCreate(array $data)
    {
        return $this->model->firstOrCreate($data);
    }

    public function update($id, array $data)
    {
        $directory = $this->model->find($id);
        if ($directory) {
            $directory->update($data);
            return $directory;
        }
        return null;
    }

    public function delete($id)
    {
        $directory = $this->model->find($id);
        if ($directory) {
            $directory->delete();
            return true;
        }
        return false;
    }

    public function insert(array $data): void
    {
        $this->model->insert($data);
    }

    public function truncate(): void
    {
        $this->db->statement('SET FOREIGN_KEY_CHECKS=0;');
        $this->model->query()->delete();
        $this->db->statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function paginate(int $perPage = 100, int $page = 1)
    {
        return $this->model->paginate($perPage, ['*'], 'page', $page);
    }
}
