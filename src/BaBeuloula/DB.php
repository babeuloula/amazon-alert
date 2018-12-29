<?php

namespace BaBeuloula;

/** @internal */
class DB
{
    /** @var \PDO */
    private $db;

    public function __construct ($filename) {
        $this->db = new \PDO("sqlite:{$filename}");
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->init();
    }

    /**
     * Insert data from database
     *
     * @param string $table
     * @param array  $fields
     * @param array  $values
     *
     * @throws \Exception
     */
    public function add (string $table, array $fields, array $values): void
    {
        $this->db->beginTransaction();

        $questionMark = [];
        for($i = 0; $i < count($fields); $i++) {
            $questionMark[] = "?";
        }
        $fields = implode(', ', $fields);
        $questionMark = implode(', ', $questionMark);

        $query = "INSERT INTO {$table} ({$fields}) VALUES ({$questionMark})";

        $sth = $this->db->prepare($query);
        $sth->execute($values);

        if (!$this->db->commit()) {
            $this->db->rollBack();
            throw new \Exception("Unable to insert data into {$table}");
        }
    }

    /**
     * Remove data from database
     *
     * @param string $table
     * @param string $field
     * @param string $value
     *
     * @throws \Exception
     */
    public function remove (string $table, string $field, string $value): void
    {
        $this->db->beginTransaction();

        $query = "DELETE FROM {$table} WHERE {$field} = ?";

        $smt = $this->db->prepare($query);
        $smt->execute([$value]);

        if (!$this->db->commit()) {
            $this->db->rollBack();
            throw new \Exception("Unable to delete data into {$table}");
        }
    }

    public function find (string $table, string $id): ?object
    {
        try {
            $smt = $this->db->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 0,1");
            $smt->execute([$id]);
            $data = $smt->fetchAll();

            if (!empty($data)) {
                return $data[0];
            }
        } catch (\Exception $e) {}


        try {
            $smt = $this->db->prepare("SELECT * FROM {$table} WHERE code = ? LIMIT 0,1");
            $smt->execute([$id]);
            $data = $smt->fetchAll();

            if (!empty($data)) {
                return $data[0];
            }
        } catch (\Exception $e) {};


        try {
            $smt = $this->db->prepare("SELECT * FROM {$table} WHERE product_code = ? LIMIT 0,1");
            $smt->execute([$id]);
            $data = $smt->fetchAll();

            if (!empty($data)) {
                return $data[0];
            }
        } catch (\Exception $e) {}

        return null;
    }

    public function findAllProducts (): array
    {
        $smt = $this->db->prepare("SELECT * FROM products");
        $smt->execute();
        return $smt->fetchAll();
    }

    public function findAll (\DateTime $from, \DateTime $to): array
    {
        $smt = $this->db->prepare("SELECT * FROM products, prices
                                             WHERE products.code = prices.product_code
                                               AND prices.created_at >= {$from->getTimestamp()}
                                               AND prices.created_at <= {$to->getTimestamp()}
                                             ORDER BY products.title");
        $smt->execute();
        return $smt->fetchAll();
    }

    private function init (): void
    {
        $this->db->beginTransaction();

        $smt = $this->db->prepare("CREATE TABLE IF NOT EXISTS `products` (
            `id`	INTEGER PRIMARY KEY AUTOINCREMENT,
            `code`	TEXT NOT NULL UNIQUE,
            `title`	TEXT NOT NULL,
            `image`	BLOB,
            `created_at`	INTEGER NOT NULL
        )");
        $smt->execute();

        $smt = $this->db->prepare("CREATE TABLE IF NOT EXISTS `prices` (
            `id`	INTEGER PRIMARY KEY AUTOINCREMENT,
            `product_code`	TEXT NOT NULL UNIQUE,
            `price`	INTEGER NOT NULL,
            `created_at`	INTEGER NOT NULL
        )");
        $smt->execute();

        $this->db->commit();
    }
}
