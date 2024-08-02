<?php

namespace App\Repositories;

use App\Models\File;
use App\Repositories\Contracts\FileRepositoryInterface;
use Illuminate\Database\DatabaseManager;

class FileRepository implements FileRepositoryInterface
{
    public function __construct(
        protected File $model,
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

    public function update($id, array $data)
    {
        $file = $this->model->find($id);
        if ($file) {
            $file->update($data);
            return $file;
        }
        return null;
    }

    public function insert(array $data): void
    {
        $this->model->insert($data);
    }

    public function delete($id)
    {
        $file = $this->model->find($id);
        if ($file) {
            $file->delete();
            return true;
        }
        return false;
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
