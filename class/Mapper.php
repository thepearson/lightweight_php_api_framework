<?php
class Mapper {

  public $db = NULL;
  public $key = 'id';

  public $table;
  public $model;
  public $config;


  /**
   * Object construct
   *
   * @param unknown_type $key
   */
  public function __construct($options = array()) {

    // allow override of model
    if (isset($options['model'])) {
      $this->model = $options['model'];
    }
    else {
      $this->model = 'Model_' . substr(get_class($this), 7);
    }

    // allow override of table
    if (isset($options['table'])) {
      $this->table = $options['table'];
    }
    else {
      $this->table = strtolower(substr(get_class($this), 7));
    }

    // allow override of table id
    if (isset($options['key'])) {
      $this->key = $options['key'];
    }

    $config = Config::getInstance();
    $this->config = $config->get('db');
  }



  public function getModel() {
    return $this->model;
  }


  /**
   * Make a database connection
   */
  public function getDb() {
    try {
      if ($this->db !== NULL) {
        return $this->db;
      }
      else {
        $this->db = new PDO("mysql:host=" . $this->config['host'] . ";dbname=" . $this->config['schema'], $this->config['user'], $this->config['password']);
        return $this->db;
      }
    }
    catch (Exception $e) {

    }
  }


  /**
   * Process a query
   *
   * @access public
   * @param String $query
   * @return array
   */
  public function getQuery($query) {
    $db = $this->getDb();
    $statement = $db->query($query);
    $count = $statement->rowCount();
    if (($statement != FALSE) && ($count > 0)) {
      $statement->setFetchMode(PDO::FETCH_CLASS, $this->model);
      if ($count == 1) {
        return $statement->fetch();
      }
      else {
        return $statement->fetchAll();
      }
    }
    return NULL;
  }


  /**
   * Helper function used to determine if the table exists
   *
   * @access public
   * @param void
   */
  public function tableExists($table = NULL, $schema = NULL) {
    if ($table == NULL) {
      $table = $this->table;
    }

    if ($schema == NULL) {
      $schema = $this->schema;
    }
    $db = $this->getDb();
    $sql = "SELECT COUNT(*) as count FROM information_schema.tables  WHERE table_schema = '" . $schema . "' AND table_name = '" . $table . "'";
    $query = $db->query($sql);
    if (($query != FALSE) && ($query->rowCount() > 0)) {
      $result = $query->fetch();
      if ($result['count'] > 0) {
        return TRUE;
      }
    }
    return FALSE;
  }


  /**
   * prepares sql for running, generally substituting the
   * table macron with the actual table name.
   *
   * @access public
   * @param string $sql
   */
  public function prepareSql($sql) {
    return str_replace(':table_name', $this->table, $sql);
  }


  /**
   * Helper function to create a table. Mostly used by metrics
   *
   * @access public
   * @param void
   * @return boolean
   */
  public function createTable() {
    if ($this->tableExists($this->table)) {
      return TRUE;
    }

    if ($this->isCustom == TRUE) {
      $sql = str_replace(':table_name', $this->table, $this->getCreateTableSql());
      $db = $this->getDb();
      $db->query($sql);
      if ($this->tableExists() == TRUE) {
        return TRUE;
      }
    }
    return FALSE;
  }



  /**
   * Public interface for retrieving the key
   *
   * @access public
   * @param void
   * @return Mixed
   */
  public function getKey() {
    return $this->key;
  }


  /**
   * Generic listing
   *
   * @access public
   * @param Int $offset
   * @param Int $limit
   * @param String $order
   * @param Boolean $asc
   * @return array
   */
  public function listing($where = NULL, $offset = 0, $limit = 10, $order = NULL, $asc = TRUE) {
    // build the where clause
    $where_string = '';
    $where_array = array();
    if (isset($where) && is_array($where)) {
      for ($i =0; $i < count($where); $i++) {
        // add the where clause to the bind array, this is used to bind field = :field after
        // the statement is prepared
        if (array_key_exists(':' . $where[$i]['field'], $where_array)) {
          $field = $where[$i]['field'];
          $comparison = array_key_exists('comparison', $where[$i]) ? $where[$i]['comparison'] : '=';
          $placeholder = ':' . $where[$i]['field'] . uniqid();
          $value = $where[$i]['value'];
        }
        else {
          $field = $where[$i]['field'];
          $comparison = array_key_exists('comparison', $where[$i]) ? $where[$i]['comparison'] : '=';
          $placeholder = ':' . $where[$i]['field'];
          $value = $where[$i]['value'];
        }
        $where_array[$placeholder] = $value;
        if ($i == 0) {
          // this is the first where so it's prefixd with WHERE
          $where_string = ' WHERE ' . $field . ' ' . $comparison . ' ' . $placeholder;
        }
        else {
          // all others are prefixed with AND
          $where_string .= ' AND ' . $field . ' ' . $comparison . ' ' . $placeholder;
        }
      }
    }

    if (!isset($order)) {
      $order = $this->key;
    }

    if (is_array($order)) {
      $order = implode(',', $order);
    }
    else {
      $order = $order;
    }

    $direction = '';
    if ($asc !== TRUE) {
      $direction = ' DESC ';
    }

    $list = array();
    $db = $this->getDb();

    if ($statement = $db->prepare('SELECT COUNT(*) FROM ' . $this->table . $where_string)) {
      if (!empty($where_array)) {
        foreach ($where_array as $key => $value) {
          $statement->bindValue($key, $value);
        }
      }
      $statement->execute();

      // get count from result
      $count = $statement->fetchColumn();

      // only if count is greater than 0 will we query for the rows
      if ($count > 0) {

        // add the selection clause
        if ($statement = $db->prepare('SELECT * FROM ' . $this->table . $where_string . ' ORDER BY ' . $order . $direction . ' LIMIT ' . $offset . ',' . $limit)) {

          // remember to select into the model
          $statement->setFetchMode(PDO::FETCH_CLASS, $this->model);

          if (!empty($where_array)) {
            foreach ($where_array as $key => $value) {
              $statement->bindValue($key, $value);
            }
          }

          $statement->execute();

          // push each returned model onto the return array
          while ($row = $statement->fetch()) {
            $list[] = $row;
          }
        }
        return array('total' => $count, 'list' => $list);
      }
    }
    return NULL;
  }


  /**
   * Loads a model given a key
   *
   * @param mixed @key
   * @return Object
   */
  public function load($key) {
    $db = $this->getDb();
    if (is_array($key)) {
      $sql = 'SELECT * FROM ' . $this->table;
      $bindKeys = array();
      foreach ($key as $k => $v) {
        $bindKeys[$key] = $value;
        if (!substr_count($sql, 'WHERE')) {
          $sql .= ' WHERE ' . $k . ' = :' . $k;
        }
        else {
          $sql .= ' AND ' . $k . ' = :' . $k;
        }
      }
      $statement = $db->prepare($sql);
      if (($statement != FALSE) && ($statement->rowCount() > 0)) {
        $statement->setFetchMode(PDO::FETCH_CLASS, $this->model);
        return $statement->execute($bindKeys);
      }
    }
    else {
      // TODO: bind values here this is nasty
      $statement = $db->query('SELECT * FROM ' . $this->table . ' WHERE ' . $this->key . ' = ' . $key);
      if (($statement != FALSE) && ($statement->rowCount() > 0)) {
        $statement->setFetchMode(PDO::FETCH_CLASS, $this->model);
        return $statement->fetch();
      }
    }
    return NULL;
  }


  /**
   * Given a list of ids retrieve each model
   *
   * @access public
   * @param array $keys
   * @param Int $offset
   * @param Int $limit
   * @param String $order
   * @param Boolean $asc
   * @return array
   */
  public function load_multiple($keys, $offset = 0, $limit = 10, $order = NULL, $asc = TRUE) {
    if (!isset($order)) {
      if (is_array($this->key)) {
        $order = implode(',', $this->key);
      }
      else {
        $order = $this->key;
      }
    }

    $direction = '';
    if ($asc !== TRUE) {
      $direction = ' DESC ';
    }

    $list = array();
    $db = $this->getDb();

    $keys = array_filter($keys, function($var) {
      return is_numeric($var);
    });

    // first lets do a count and see how many rows there are.
    if ($statement = $db->query('SELECT COUNT(*) FROM ' . $this->table . ' WHERE id IN (' . implode(', ', $keys) . ')')) {

      // get count from result
      $count = $statement->fetchColumn();

      // only if count is greater than 0 will we query for the rows
      if ($count > 0) {

        // add the selection clause
        if ($statement = $db->query('SELECT * FROM ' . $this->table . ' WHERE id IN (' . implode(', ', $keys) . ') ORDER BY ' . $order . $direction . ' LIMIT ' . $offset . ',' . $limit)) {

          // remember to select into the model
          $statement->setFetchMode(PDO::FETCH_CLASS, $this->model);

          // push each returned model onto the return array
          while ($row = $statement->fetch()) {
            $list[] = $row;
          }
        }
        return array('total' => $count, 'list' => $list);
      }
    }
    return NULL;
  }


  /**
   * Saves a given model
   *
   * @param Object $model
   */
  public function save($model, $op = FALSE) {

    if ($op == FALSE)   {
      if (property_exists($model, $this->key) && $this->load($model->{$this->key})) {
        $op = 'update';
      }
      else {
        $op = 'insert';
      }
    }

    // Update function

    if ($op == 'update') {
      $op = 'update';
      $vars = get_object_vars($model);

      $fields = '';
      foreach ($vars as $key => $value) {
        // if the value is set then lets add it to the sql params list
        if ($key != $this->key) {
          $fields .= $key . ' = :' . $key . ', ';
        }
      }
      // update
      $sql = 'UPDATE `' . $this->table . '` SET ' . rtrim($fields, ', ') . ' WHERE ' . $this->key . ' = :' . $this->key;
    }
    else {
      // remove the key
      $op = 'insert';
      $vars = get_object_vars($model);
      $values = '';
      $fields = '';
      foreach ($vars as $key => $value) {
        // if the value is set then lets add it to the sql params list
        $fields .= $key . ', ';
        $values .= ':' . $key . ', ';
      }
      // insert
      $sql = 'INSERT INTO `' . $this->table . '` (' . rtrim($fields, ', ') . ') VALUES (' . rtrim($values, ', ') . ')';
    }

    $db = $this->getDb();
    $statement = $db->prepare($sql);
    if ($statement->execute((array)$model)) {
      if (!is_array($this->key)) {
        $key = $this->key;
        if (empty($model->{$key})) {
          $model->{$key} = $db->lastInsertId();
        }
      }
      return $model;
    }
    else {
      return NULL;
    }
  }


  /**
   * Deletes a model from the DB
   *
   * @param Object $model
   */
  public function delete($key) {
    $db = $this->getDb();
    $statement = $db->prepare('DELETE FROM `' . $this->table . '` WHERE ' . $this->key . ' = ' . $key);
    $statement->bindValue(':' . $this->key, $key);
    $statement->execute();
    if ($statement->rowCount() > 0) {
      return TRUE;
    }
    return NULL;
  }
}
