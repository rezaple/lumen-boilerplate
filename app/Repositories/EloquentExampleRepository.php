<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EloquentExampleRepository extends AbstractEloquentRepository implements BaseRepository
{

    protected $modelName = User::class;

    /*
     * @inheritdoc
     */
    public function save(array $data)
    {
        //code
    }

    /**
     * @inheritdoc
     */
    public function update(Model $model, array $data)
    {
        //code
    }

    public function updatePassword(Model $model, array $data)
    {
        //code
    }

    public function updateProfile(Model $model, $request)
    {
        //code
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $searchCriteria = [])
    {
        //code
    }

    /**
     * @inheritdoc
     */
    public function findOne($id)
    {
        //code
    }

    public function getList(array $searchCriteria = [])
    {
        # code...
    }
}