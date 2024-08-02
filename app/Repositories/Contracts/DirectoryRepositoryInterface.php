<?php

namespace App\Repositories\Contracts;

interface DirectoryRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function firstOrCreate(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function insert(array $data): void;

    public function truncate(): void;

    public function paginate(int $perPage, int $page);
}
