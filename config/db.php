<?php

class DB {

    public $conn;

    private $host = 'mysql';
    private $name = 'mood';
    private $user = 'mood';
    private $pass = 'secret';

    public function connect() {
        $conn_str = "mysql:host=$this->host;dbname=$this->name";
        $this->conn = new PDO($conn_str, $this->user, $this->pass);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function close() {
        $this->conn = null;
    }
}