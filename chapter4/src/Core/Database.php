<?php

namespace App\Core;

use PDO;
use PDOException;

class Database{
  private PDO $connection;
  private static ?Database $instance = null;

  private function __construct()
  {
    $dsn = "mysql:host=localhost;port=3307;dbname=php_workshop;charset=utf8mb4";
    $options = [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    try {
      $this->connection = new PDO($dsn, 'root', '', $options);
    } catch (PDOException $e) {
      die("Database connection failed: " . $e->getMessage());
    }
  }

  public static function getInstance() : self{
    if (self::$instance == null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function getConnection() :PDO {
    return $this->connection;
  }
}