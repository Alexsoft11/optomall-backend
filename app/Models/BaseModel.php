<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseModel extends Model
{
    public function save(array $options = [])
    {
        DB::beginTransaction();

        try {
            parent::save($options);

            $this->saveToSecondDatabase();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function saveToSecondDatabase()
    {
        $attributes = $this->getAttributes();

        DB::connection('mysql2')->table($this->getTable())->insert($attributes);
    }
}
