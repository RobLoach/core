<?php

namespace Drupal\Database\Driver\sqlite;

use Drupal\Database\Query\Insert as QueryInsert;

/**
 * SQLite specific implementation of InsertQuery.
 *
 * We ignore all the default fields and use the clever SQLite syntax:
 *   INSERT INTO table DEFAULT VALUES
 * for degenerated "default only" queries.
 */
class Insert extends QueryInsert {

  public function execute() {
    if (!$this->preExecute()) {
      return NULL;
    }
    if (count($this->insertFields)) {
      return parent::execute();
    }
    else {
      return $this->connection->query('INSERT INTO {' . $this->table . '} DEFAULT VALUES', array(), $this->queryOptions);
    }
  }

  public function __toString() {
    // Create a sanitized comment string to prepend to the query.
    $comments = $this->connection->makeComment($this->comments);

    // Produce as many generic placeholders as necessary.
    $placeholders = array_fill(0, count($this->insertFields), '?');

    // If we're selecting from a SelectQuery, finish building the query and
    // pass it back, as any remaining options are irrelevant.
    if (!empty($this->fromQuery)) {
      return $comments . 'INSERT INTO {' . $this->table . '} (' . implode(', ', $this->insertFields) . ') ' . $this->fromQuery;
    }

    return $comments . 'INSERT INTO {' . $this->table . '} (' . implode(', ', $this->insertFields) . ') VALUES (' . implode(', ', $placeholders) . ')';
  }

}