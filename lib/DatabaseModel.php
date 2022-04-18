<?php

abstract class DatabaseModel {
    abstract public function getTableName();
    abstract public function getFields();
    public function getRows() { return []; }

    // Migrating model to database
    public static function migrate(DatabaseModel $model) {
        // Generate SQL request
        $sql = 'CREATE TABLE ' . $model->getTableName() . ' (';
        
        // Fields (columns)
        $fields = $model->getFields();
        $i = 0;
        $length = count($fields);
        foreach($fields as &$field) {
            // Field name, Field type
            $sql .= $field['name'] . ' ' . $field['type'];
            // Field length
            if(isset($field['length'])) { $sql .= ' (' . strval($field['length']) . ') '; }
            // Int should be unsigned
            if($field['type'] == 'INT') {  $sql .= 'UNSIGNED '; }
            // Auto increment
            if(array_key_exists('auto_increment', $field) && $field['auto_increment'] == true) {
                $sql .= 'AUTO_INCREMENT ';
            }
            // Primary
            if(array_key_exists('primary', $field) && $field['primary'] == true) {
                $sql .= 'PRIMARY KEY ';
            }
            // End
            if($length > 1 && $i < $length - 1) { $sql .= ','; }

            $i++;
        }
        $sql .= ');';

        // Call database
        Database::execute($sql);

        // Rows (Optinal)
        $rows = $model->getRows();
        if(count($rows) > 0) {
            foreach($rows as &$row) {
                DatabaseModel::insert($model, $row);
            }
        }
    }

    // Find row
    public static function select(DatabaseModel $model, array $where, bool $multiple = false) {
        $sql = 'SELECT * FROM ' . $model->getTableName() . ' WHERE ';

        // Which values
        $i = 0;
        $length = count($where);
        foreach(array_keys($where) as &$colName) {
            $sql .= $colName . "=? ";

            if($length > 1 && $i < $length - 1) { $sql .= ','; }
            $i++;
        }

        if($multiple)
            return Database::executeFetchAll($sql, array_values($where));
        else
            return Database::executeFetch($sql, array_values($where));
    }
    public static function selectAll(DatabaseModel $model): array {
        $sql = 'SELECT * FROM ' . $model->getTableName();
        return Database::executeFetchAll($sql);
    }
    public static function selectLast(DatabaseModel $model) {
        $sql = 'SELECT * FROM ' . $model->getTableName() . ' WHERE id=(SELECT LAST_INSERT_ID());';
        return Database::executeFetch($sql);
    }
    // Insert row
    public static function insert(DatabaseModel $model, array $values) {
        $sql = 'INSERT INTO ' . $model->getTableName() . ' (';
        $length = count($values);

        // Which columns
        $keys = array_keys($values);
        $i = 0;
        foreach($keys as &$colName) {
            $sql .= $colName;
            if($length > 1 && $i < $length - 1) { $sql .= ','; }
            $i++;
        }
        $sql .= ') VALUES (';

        // Which values
        $i = 0;
        foreach(array_values($values) as &$colValue) {
            // JSON value
            if(is_string($colValue) && Utils::str_starts_with($colValue, 'JSON')) {
                $sql .=  $colValue;

                unset($values[$keys[$i]]);
            }
            // Else inject value
            else {
                $sql .= '?';
            }

            if($length > 1 && $i < $length - 1) { $sql .= ','; }
            $i++;
        }
        $sql .= ');';

        try {
            Database::execute($sql, array_values($values));
        }
        catch(PDOException $e) {
            // Return PDOException code
            return $e->errorInfo[1]; 
        }
    }
    // Update row
    public static function update(DatabaseModel $model, array $values, array $where): void {
        $sql = 'UPDATE ' . $model->getTableName() . ' SET ';

        // SET
        $i = 0;
        $keys = array_keys($values);
        $length = count($values);
        foreach($keys as &$colName) {
            $colValue = $values[$colName];
            // JSON value
            if(is_string($colValue) && Utils::str_starts_with($colValue, 'JSON')) {
                $sql .= $colName . '=' .$colValue;

                unset($values[$keys[$i]]);
            }
            // Else inject value
            else {
                $sql .= $colName . '=?';
            }

            if($length > 1 && $i < $length - 1) { $sql .= ','; }
            $i++;
        }

        // WHERE
        $sql .= ' WHERE ';
        $i = 0;
        $length = count($where);
        foreach(array_keys($where) as &$colName) {
            $sql .= $colName . "=? ";

            if($length > 1 && $i < $length - 1) { $sql .= ','; }
            $i++;
        }
        
        Database::execute($sql, array_values(array_merge($values, $where)));
    }
    // Append value to JSON 
    public static function selectArrayContains(DatabaseModel $model, string $column, string $value): array {
        $sql = 'SELECT * FROM `' . $model->getTableName() . '` WHERE JSON_CONTAINS(`' . $column . '`, ' . "'" . $value . "'" . ');';

        return Database::executeFetchAll($sql);
    }
    public static function arrayAppend(DatabaseModel $model, string $column, $appendValue, array $where): void {
        $sql = 'UPDATE ' . $model->getTableName() . ' SET ' . $column . ' = JSON_ARRAY_APPEND(' . $column . ', "$", ' . "'" . json_encode($appendValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "'" . ')';
        
        // Where values
        $sql .= ' WHERE ';
        $i = 0;
        $length = count($where);
        foreach(array_keys($where) as &$colName) {
            $sql .= $colName . "=? ";

            if($length > 1 && $i < $length - 1) { $sql .= ','; }
            $i++;
        }
        $sql .= ';';

        Database::execute($sql, array_values($where));
    }
    public static function arrayRemove(DatabaseModel $model, string $column) {
        $sql = "UPDATE " . $model->getTableName() . "
        SET " . $column . " = JSON_REMOVE( 
            " . $column . ", REPLACE( 
                JSON_CONTAINS( " . $column . ", 'one', 'a456', null, '$**.users' )
                , '" . '"' . "'
                , ''
            ) 
          );";
    }
    // Delete row
    public static function delete(DatabaseModel $model, array $where): void {
        $sql = 'DELETE FROM ' . $model->getTableName() . ' WHERE ';

        // Which values
        $i = 0;
        $length = count($where);
        foreach(array_keys($where) as &$colName) {
            $sql .= $colName . "=? ";

            if($length > 1 && $i < $length - 1) { $sql .= ','; }
            $i++;
        }

        Database::execute($sql, array_values($where));
    }
}