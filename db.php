<?php

class DB {
  private $_dbh;
  private $_stmt;
  private static $_instance = null;

  private function __construct()
  {
    try {
      $p = require 'settings.php';
      $this->_dbh = new PDO(
        $p['DB_DRIVER'] . ':host=' .
        $p['DB_HOST'] . ';dbname=' .
        $p['DB_NAME'],
        $p['DB_USER'],
        $p['DB_PASSWORD'],
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
      );
      $this->_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      echo 'FAILED TO GET DB HANDLE: >' . $e->getMessage();
    }
  }

  private function __clone() {}

  public static function getInstance()
  {
    if (self::$_instance == null) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

//  public function save($table, $data) {
//    $this->_stmt = $this->_dbh->prepare('INSERT INTO ' . $table .
//      '(' . implode(',', array_keys($data)) . ')' .
//      ' VALUES (?' . str_repeat(',?', count($data) - 1) . ')');
//    $this->_stmt->execute(array_values($data));
//  }

  public function save($table, $data)
  {
//    if (!$this->update($table, $data)) {
      $this->insert($table, $data);
//    }
  }

  public function insert($table, $data) {
    $this->_stmt = $this->_dbh->prepare('INSERT INTO ' . $table .
      '(' . implode(',', array_keys($data)) . ')' .
      ' VALUES (?' . str_repeat(',?', count($data) - 1) . ')');
    return $this->_stmt->execute(array_values($data));
  }

  public function update($table, $data) {
    $this->_stmt = $this->_dbh->prepare('UPDATE ' . $table .
      ' SET ' . implode('=?,', array_keys($data)) . '=?');
    return $this->_stmt->execute(array_values($data));
  }
}