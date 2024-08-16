<?php

namespace App\Core;
use App\Config\Config;
use Exception;
use mysqli;


class Database {
    private $connection;
    

    public function __construct() {
        $config = new Config();
        try{
        $this->connection = new mysqli($config->host, $config->username, $config->password,$config->database);
        }catch(Exception $e){
            print_r($e);
            die("Something went wrong in the database");
        }
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);

        if ($stmt === false) {
            die("SQL error: " . $this->connection->error);
        }

         if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param) || is_bool($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b'; // Default to binary
            }
        }

            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        return $stmt;
    }

    public function fetchAllSql($sql, $params = []) {

        $stmt = $this->query($sql, $params);

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOneSql($sql, $params = []) {

        $stmt = $this->query($sql, $params);

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function fetchAll($table, $where = []) {

        $whereClause = implode(' AND ', array_map(fn($col) => "$col = ?", array_keys($where)));
        $sql = "SELECT * FROM  $table WHERE $whereClause";

        if(count($where)==0) $sql = "SELECT * FROM  $table";

        $stmt = $this->query($sql, array_values($where));

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne($table, $where = []) {

        $whereClause = implode(' AND ', array_map(fn($col) => "$col = ?", array_keys($where)));
        $sql = "SELECT * FROM $table WHERE $whereClause";

        if(count($where)==0) $sql = "SELECT * FROM  $table";

        $stmt = $this->query($sql, array_values($where));

        $result = $stmt->get_result(); 
        return $result->fetch_assoc();

    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $this->query($sql, array_values($data));

        return $this->connection->insert_id;
    }

    public function update($table, $data, $where) {
        $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
        $whereClause = implode(' AND ', array_map(fn($col) => "$col = ?", array_keys($where)));
        $sql = "UPDATE $table SET $set WHERE $whereClause";

        $stmt =$this->query($sql, array_merge(array_values($data), array_values($where)));

        return $stmt->affected_rows;
    }

    public function delete($table, $where) {
        $whereClause = implode(' AND ', array_map(fn($col) => "$col = ?", array_keys($where)));
        $sql = "DELETE FROM $table WHERE $whereClause";

        $stmt = $this->query($sql, array_values($where));

        return $stmt->affected_rows;
    }

    public function close() {
        $this->connection->close();
    }
}
