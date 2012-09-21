<?php
namespace mpr\db;

use \mpr\config;

/**
 * MongoDb class wrapper
 *
 * @author GreeveX <greevex@gmail.com>
 */
class mongoDb
{

    /**
     * @var \mpr\db\mongoDb
     */
    private static $instance;

    private $mongo;
    private $db;

    /**
     * @static
     * @return mongoDb
     */
    public static function getInstance()
    {
        if(self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $packageConfig = config::getPackageConfig(__CLASS__);
        $this->mongo = new \Mongo($packageConfig['host']);
        $this->db = $this->mongo
                ->selectDB($packageConfig['dbname']);
    }

    public function reconnect()
    {
        $this->mongo->connect();
        $this->db->resetError();
    }

    public function insert($collection, &$data, $options = [])
    {
        if(!isset($data['_id'])) {
            $data['_id'] = new \MongoId();
        } elseif(!is_object($data['_id'])) {
            $data['_id'] = new \MongoId($data['_id']);
        }
        return $this->db
                    ->selectCollection($collection)
                    ->insert($data, $options);
    }

    /**
    * select data from Mongo
    *
    * @param string $collection
    * @param array $criteria
    * @param array $fields
    * @return \MongoCursor
    */
    public function select($collection, $criteria = [], $fields = [])
    {
        $data = $this->db
                    ->selectCollection($collection)
                    ->find($criteria, $fields);
        return $data;
    }

    public function selectOne($collection, $criteria = [], $fields = [])
    {

        $data = $this->db
                    ->selectCollection($collection)
                    ->findOne($criteria, $fields);
        return $data;
    }

    public function update($collection, $criteria = [], $update_data = [], $createIfNonExists = false)
    {
        $data = $this->db
                    ->selectCollection($collection)
                    ->update($criteria, $update_data);
        return $data;
    }

    public function remove($collection, $criteria, $options = [])
    {
        $data = $this->db
                    ->selectCollection($collection)
                    ->remove($criteria, $options);
        return $data;
    }

    public function getCount($collection)
    {
        try {
            return $this->db
                ->selectCollection($collection)
                ->count();
        } catch(\Exception $e) {
            return false;
        }
    }

    public function getCountBy($collection, $criteria)
    {
        return $this->select($collection, $criteria)->count();
    }

    public function getDatabase()
    {
        return $this->db;
    }
}
