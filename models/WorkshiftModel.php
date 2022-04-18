<?php

class WorkshiftModel extends DatabaseModel {
    public static function getInstance() {
        return new WorkshiftModel();
    }

    public function getTableName() {
        return 'workshifts';
    }
    public function getFields() {
        return [
            array(
                'name' => 'id',
                'type' => 'INT',

                'primary' => true,
                'auto_increment' => true
            ),
            array(
                'name' => 'workers',
                'type' => 'JSON',
            ),
            array(
                'name' => 'date',
                'type' => 'DATE',
            )
        ];
    }
}