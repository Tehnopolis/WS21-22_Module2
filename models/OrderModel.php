<?php

class OrderModel extends DatabaseModel {
    public static function getInstance() {
        return new OrderModel();
    }

    public function getTableName() {
        return 'orders';
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
                'name' => 'workshift_id',
                'type' => 'INT',
            ),
            array(
                'name' => 'products',
                'type' => 'JSON',
            ),
            array(
                'name' => 'status',
                'type' => 'VARCHAR', // created/cooking/done/paid/cancelled
                'length' => 25
            )
        ];
    }
}