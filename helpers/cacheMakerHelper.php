<?php

class CacheMakerHelper
{
    private $db;
    private $cacheName;
    private $updateInterval;

    public function __construct($cache_name, $cache_db, $update_interval_minutes)
    {
        $this->db = new SQLite3($cache_db);
        $this->cacheName = $cache_name;
        $this->updateInterval = $update_interval_minutes;
        $this->createCacheTableIfNotExists();
    }

    public function execute($callback, $arguments)
    {
        $parameters = json_encode($arguments);
        $key = md5($parameters);

        if ($this->validateIfCacheExists($key)) {
            if ($this->validateIfCacheNeedsUpdate($key)) {
                $data = $callback(...$arguments);
                $this->updateCache($key, $data);
                return $data;
            } else {
                return $this->getCache($key);
            }
        } else {
            $data = $callback(...$arguments);
            $this->insertCache($key, $data);
            return $data;
        }
    }

    private function createCacheTableIfNotExists()
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS {$this->cacheName} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cache_key TEXT NOT NULL UNIQUE,
                value TEXT NOT NULL,
                updated_at DATETIME
            )
        ");
    }

    private function validateIfCacheExists($key)
    {
        $stmt = $this->db->prepare("SELECT updated_at FROM {$this->cacheName} WHERE cache_key = :key");
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC) !== false;
    }

    private function validateIfCacheNeedsUpdate($key)
    {
        $stmt = $this->db->prepare("SELECT updated_at FROM {$this->cacheName} WHERE cache_key = :key");
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if (!$row) {
            return true;
        }

        $lastUpdate = new DateTime($row['updated_at']);
        $now = new DateTime();
        return ($now->getTimestamp() - $lastUpdate->getTimestamp()) > ($this->updateInterval * 60);
    }

    private function updateCache($key, $value)
    {
        $stmt = $this->db->prepare("UPDATE {$this->cacheName} SET value = :value, updated_at = :time WHERE cache_key = :key");
        $stmt->bindValue(':time', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':value', json_encode($value), SQLITE3_TEXT);
        $stmt->execute();
    }

    private function insertCache($key, $value)
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->cacheName} (cache_key, updated_at, value) VALUES (:key, :time, :value)");
        $stmt->bindValue(':time', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':value', json_encode($value), SQLITE3_TEXT);
        $stmt->execute();
    }

    private function getCache($key)
    {
        $stmt = $this->db->prepare("SELECT value FROM {$this->cacheName} WHERE cache_key = :key");
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? json_decode($row['value'], true) : null;
    }
}
