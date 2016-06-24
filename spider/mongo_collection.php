<?php

class Mongo_Collection {
    public $collection;
    private static $config_file_path = 'db_config.php';

    public function __construct($table)
    {
        $config = require self::$config_file_path;
        $username = $config['mongo']['username'];
        $pwd = $config['mongo']['password'];
        $host = $config['mongo']['host'];
        $port = $config['mongo']['port'];
        $db = $config['mongo']['dbname'];
        $option = $config['mongo']['option'];
        $dsn = 'mongodb://'.$username.':'.$pwd.'@'.$host.':'.$port.'/'.$db;
        $client = new MongoClient($dsn, $option);
        $db = $client->selectDB($db);
        $this->collection = new MongoCollection($db, $table);
    }

    public function findPage($conditions = [], $page)
    {
        $offset = ($page - 1) * 20;
        $cursor = $this->collection->find($conditions)->skip($offset)->limit(20);
        $array = iterator_to_array($cursor);
        return $array;
    }

    public function findOne($conditions = [])
    {
        $array = $this->collection->findOne($conditions);
        return $array;
    }

    public function findAll($conditions = [])
    {
        $cursor = $this->collection->find($conditions);
        $array = iterator_to_array($cursor);
        return $array;
    }

    public function count($conditions = [])
    {
        return $this->collection->count($conditions);
    }

    public function insert($data, $options = [])
    {
        $options = array_merge(['w' => 1], $options);
        return $this->collection->insert($data, $options);
    }

    public function insertAll($data, $options = [])
    {
        if (empty($data)) {
            return 0;
        }
        $options = array_merge(['w' => 1], $options);
        return $this->collection->batchInsert($data, $options);
    }

    public function delete($conditions, $options)
    {
        $options = array_merge(['w' => 1, 'justOne' => false], $options);
        return $this->collection->remove($conditions, $options);
    }

    public function update($conditions, $data, $options = [])
    {
        $options = array_merge(['w' => 1, 'multiple' => true], $options);
        return $this->collection->update($conditions, $data, $options);
    }

    public function aggregate($pipe)
    {
        $result = $this->collection->aggregate($pipe);

        return empty($result['result']) ? [] : $result['result'];
    }
}