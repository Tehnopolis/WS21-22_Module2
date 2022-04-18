<?php

class UserModel extends DatabaseModel {
    public static function getInstance() {
        return new UserModel();
    }

    public function getTableName() {
        return 'users';
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
                'name' => 'phone',
                'type' => 'VARCHAR',
                'length' => 50
            ),
            array(
                'name' => 'password',
                'type' => 'VARCHAR',
                'length' => 255
            ),
            array(
                'name' => 'role',
                'type' => 'VARCHAR', // admin/waiter/cook
                'length' => 25
            )
        ];
    }
    public function getRows() {
        return [
            array(
                'phone' => '+11001001010',
                'password' => 'admin',
                'role' => 'admin'
            ),
            array(
                'phone' => '+21001001010',
                'password' => 'waiter',
                'role' => 'waiter'
            ),
            array(
                'phone' => '+31001001010',
                'password' => 'cook',
                'role' => 'cook'
            )
        ];
    }
}