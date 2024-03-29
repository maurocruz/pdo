<?php

declare(strict_types=1);

namespace Plinct\PDO;

class Crud
{
  /**
   * @var string
   */
  protected string $table;

  /**
   * @param string $table
   * @return Crud
   */
  public function setTable(string $table): Crud
  {
    $this->table = $table;
    return $this;
  }

  /**
   * READ
   * @param string $field
   * @param string|null $where
   * @param string|null $groupBy
   * @param string|null $orderBy
   * @param null $limit
   * @param null $offset
   * @param array|null $args
   * @return array
   */
  public function read(
    string $field = "*",
    string $where = null,
    string $groupBy = null,
    string $orderBy = null,
    $limit = null,
    $offset = null, array
      $args = null
  ): array
  {
    $query = "SELECT $field FROM `$this->table`";
    $query .= $where ? " WHERE $where" : null;
    $query .= $groupBy ? " GROUP BY $groupBy" : null;
    $query .= $orderBy ? " ORDER BY $orderBy" : null;
    $query .= $limit ? " LIMIT $limit" : null;
    $query .= $offset ? " OFFSET $offset" : null;
    $query .= ";";
    return PDOConnect::run($query, $args);
  }

  /**
   * CREATED
   * @param array $data
   * @return array
   */
  public function created(array $data): array
  {
    $names = null;
    $values = null;
    $bindValues = null;
    if (empty($data)) {
      return [ "message" => "Record in $this->table not created because data is empty" ];
    }
    // query
    foreach ($data as $key => $value) {
	    $names[] = "`$key`";
      $values[] = "?";
      $bindValues[] = $value;
    }
    $columns = implode(",", $names);
    $rows = implode(",", $values);
    $query = "INSERT INTO `$this->table` ($columns) VALUES ($rows)";
      return PDOConnect::run($query, $bindValues);
  }

  /**
   * UPDATE
   * @param array $data
   * @param string $where
   * @return array
   */
  public function update(array $data, string $where): array
  {
    $names = null;
    $bindValues = null;
    if (empty($data)) {
      return [ "message" => "No data from update in CRUD" ];
    }
    // query
    foreach ($data as $key => $value) {
      $names[] = "`$key`=?";
      $bindValues[] = $value;
    }
    $namesString = implode(",", $names);
    $query = "UPDATE `$this->table` SET $namesString WHERE $where;";
    return PDOConnect::run($query, $bindValues);
  }

  /**
   * DELETE
   * @param string | array $where
   * @param null $limit
   * @return array
   */
  public function erase($where, $limit = null): array
  {
		if (is_array($where)) {
			$whereArray = null;
			foreach ($where as $key => $value) {
				$whereArray[] = "`$key`='$value'";
			}
			$where = implode(" AND ",$whereArray);
		}
    $query = "DELETE FROM `$this->table` WHERE $where";
    $query .= $limit ? " LIMIT $limit" : null;
    $query .= ";";
    $run = PDOConnect::run($query);

	  if (empty($run)) {
      return ['status'=>'success', 'message'=>'Deleted successfully'];
    } else {
      return $run;
    }
  }
}
