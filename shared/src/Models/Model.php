<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

use StMarks\Shared\Config\Database;
use PDO;
use PDOStatement;

/**
 * Base Active-Record-ish model over PDO (same shape as eSpace's Model.php), with three fixes:
 * - paginate()'s "search" filter is driven by each model's own $searchable columns instead of a
 *   hardcoded username/email/role assumption baked into the generic base.
 * - soft-delete filtering only applies when a model opts in via $softDeletes = true (none of
 *   stmarkswebsite's current tables have a deleted_at column).
 * - table/column names are backtick-quoted (eSpace's never are) - stmarkswebsite has a real
 *   column named `order` (slides.order), a reserved word in MySQL, which fails outright without
 *   this. Values are still always bound as parameters, never interpolated - this only quotes
 *   identifiers (table/column names), which are never user input.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected bool $timestamps = true;
    protected bool $softDeletes = false;
    /** Columns paginate()'s 'search' filter runs a LIKE across (OR'd together). */
    protected array $searchable = [];
    protected string $createdAt = 'created_at';
    protected string $updatedAt = 'updated_at';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDb(): PDO
    {
        return $this->db;
    }

    /** Backtick-quotes an identifier (table/column name) - never call this on a value. */
    protected function qi(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->qi($this->table)} WHERE {$this->qi($this->primaryKey)} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ? $this->hideFields($result) : null;
    }

    public function all(array $conditions = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
    {
        [$sql, $params] = $this->buildSelect($conditions);

        if (!empty($orderBy)) {
            $order = [];
            foreach ($orderBy as $field => $direction) {
                $order[] = "{$this->qi($field)} {$direction}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $order);
        }

        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map([$this, 'hideFields'], $stmt->fetchAll());
    }

    public function where(array $conditions): array
    {
        return $this->all($conditions);
    }

    public function first(array $conditions): ?array
    {
        $results = $this->all($conditions, [], 1);
        return $results[0] ?? null;
    }

    public function create(array $data): int|false
    {
        $data = $this->filterFillable($data);

        if ($this->timestamps) {
            $data[$this->createdAt] = date('Y-m-d H:i:s');
            $data[$this->updatedAt] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $columns = array_map(fn ($field) => $this->qi($field), $fields);
        $placeholders = array_map(fn ($field) => ":{$field}", $fields);

        $sql = "INSERT INTO {$this->qi($this->table)} (" . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($data)) {
            return (int) Database::lastInsertId();
        }

        return false;
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);

        if ($this->timestamps) {
            $data[$this->updatedAt] = date('Y-m-d H:i:s');
        }

        if (empty($data)) {
            return true;
        }

        $set = array_map(fn ($field) => "{$this->qi($field)} = :{$field}", array_keys($data));

        $data['id'] = $id;
        $sql = "UPDATE {$this->qi($this->table)} SET " . implode(', ', $set) . " WHERE {$this->qi($this->primaryKey)} = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->qi($this->table)} WHERE {$this->qi($this->primaryKey)} = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function softDelete(int $id): bool
    {
        return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function restore(int $id): bool
    {
        return $this->update($id, ['deleted_at' => null]);
    }

    public function count(array $conditions = []): int
    {
        [$sql, $params] = $this->buildSelect($conditions, 'COUNT(*) as count');
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetch()['count'];
    }

    public function exists(int $id): bool
    {
        return $this->find($id) !== null;
    }

    public function paginate(int $page = 1, int $limit = 20, array $filters = [], string $orderBy = 'created_at DESC'): array
    {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];

        foreach ($filters as $field => $value) {
            if ($field === 'search') {
                if ($value !== '' && $value !== null && !empty($this->searchable)) {
                    $clauses = array_map(fn ($col) => "{$this->qi($col)} LIKE :search", $this->searchable);
                    $where[] = '(' . implode(' OR ', $clauses) . ')';
                    $params['search'] = "%{$value}%";
                }
            } else {
                $where[] = "{$this->qi($field)} = :{$field}";
                $params[$field] = $value;
            }
        }

        if ($this->softDeletes) {
            $where[] = 'deleted_at IS NULL';
        }

        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->qi($this->table)}{$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['total'];

        $stmt = $this->db->prepare("SELECT * FROM {$this->qi($this->table)}{$whereSql} ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}");
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        return [
            'data' => array_map([$this, 'hideFields'], $data),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int) ceil($total / max($limit, 1)),
            ],
        ];
    }

    protected function buildSelect(array $conditions, string $select = '*'): array
    {
        $sql = "SELECT {$select} FROM {$this->qi($this->table)}";
        $params = [];

        $where = [];
        foreach ($conditions as $field => $value) {
            $where[] = "{$this->qi($field)} = :{$field}";
            $params[$field] = $value;
        }

        if ($this->softDeletes) {
            $where[] = 'deleted_at IS NULL';
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return [$sql, $params];
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function hideFields(array $data): array
    {
        if (empty($this->hidden)) {
            return $data;
        }

        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    public function beginTransaction(): bool
    {
        return Database::beginTransaction();
    }

    public function commit(): bool
    {
        return Database::commit();
    }

    public function rollback(): bool
    {
        return Database::rollback();
    }
}
