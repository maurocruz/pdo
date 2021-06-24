<?php
namespace Plinct\PDO;

class Crud {
    protected $table;

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
    protected function read(string $field = "*", string $where = null, string $groupBy = null, string $orderBy = null, $limit = null, $offset = null, array $args = null): array {
        $query = "SELECT $field FROM $this->table";
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
    protected function created(array $data): array {
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
        $query = "INSERT INTO $this->table ($columns) VALUES ($rows)";
        return PDOConnect::run($query, $bindValues);
    }
    /**
     * UPDATE
     * @param array $data
     * @param string $where
     * @return array
     */
    protected function update(array $data, string $where): array {
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
        $query = "UPDATE `" . $this->table . "` SET ";
        $query .= implode(",", $names);
        $query .= " WHERE $where;";
        return PDOConnect::run($query, $bindValues);
    }
    /**
     * DELETE
     * @param string $where
     * @param null $limit
     * @return array
     */
    protected function erase(string $where, $limit = null): array {
        $query = "DELETE FROM $this->table WHERE $where";
        $query .= $limit ? " LIMIT $limit" : null;
        $query .= ";";
        $run = PDOConnect::run($query);
        if (empty($run)) {
            return [ "message" => "Deleted successfully" ];
        } else {
            return $run;
        }
    }
}
