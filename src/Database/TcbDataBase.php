<?php
namespace MiniGame\Database;

use TcbManager\TcbManager;
use TencentCloudBase\Database\Db;

class TcbDataBase
{
    protected static $instance;
    protected static $commandFunc = [
        '='   => 'eq',
        '=='  => 'eq',
        '!='  => 'neq',
        '>'   => 'gt',
        '>='  => 'gte',
        '<'   => 'lt',
        '<='  => 'lte',
        'in'  => 'in',
        'nin' => 'nin'
    ];

    static public function init()
    {
        if(!self::$instance || !(self::$instance instanceof Db))
        {
            $confPath = ROOT_PATH . '/config/tcbconf.php';
            $conf     = file_exists($confPath) ? require_once $confPath : [];
            $tcbManager = TcbManager::init($conf);
            $databaseManager = $tcbManager->getDatabaseManager();
            $db = $databaseManager->db();
            self::$instance = $db;
        }
        return self::$instance;
    }

    /**
     * @param $collectionName
     * @return array
     * @throws \TencentCloudBase\Utils\TcbException
     */
    static public function all(string $collectionName):array
    {
        $db     = self::init();
        $result = $db->collection($collectionName)->get();
        $data   = $result['data'] ?? [];
        return $data;
    }

    /**
     * 根据id查询记录
     * @param $collectionName
     * @param $id
     * @return int|mixed
     * @throws \TencentCloudBase\Utils\TcbException
     */
    static public function find(string $collectionName , string $id):array
    {
        $db     = self::init();
        $result = $db->collection($collectionName)->doc($id)->get();
        $data   = $result['data'] ?? [];
        $first  = $data[0] ?? 0;
        return $first;
    }

    /**
     * @param string $collectionName
     * @param array $condition
     * @param array $orderBy  [field , orderType]
     * @return array
     * @throws \TencentCloudBase\Utils\TcbException
     */
    static public function get(string $collectionName , array $condition = [] , array $orderBy = []):array
    {
        $db     = self::init();
        $condition = self::formatCondition($condition , $db);
        if($orderBy){
            $result = $db->collection($collectionName)->where($condition)->orderBy($orderBy[0] , $orderBy[1])->get();
        }else{
            $result = $db->collection($collectionName)->where($condition)->get();
        }
        $data   = $result['data'] ?? [];
        return $data;
    }

    /**
     * @param string $collectionName
     * @param array $condition
     * @return array
     * @throws \TencentCloudBase\Utils\TcbException
     */
    static public function count(string $collectionName , array $condition = [])
    {
        $db     = self::init();
        $condition = self::formatCondition($condition , $db);
        $result = $db->collection($collectionName)->where($condition)->count();
        $data   = $result['total'] ?? 0;
        return $data;
    }

    static public function add(string $collectionName , array $data):array
    {
        $db     = self::init();
        $result = $db->collection($collectionName)->add($data);
        return $result['data'] ?? [];
    }

    static public function removeById(string $collectionName , $id)
    {
        $db = self::init();
        $result = $db->collection($collectionName)->doc($id)->remove();
        return $result['deleted'] ?? 0;
    }

    static public function remove(string $collectionName , $condition):int
    {
        $db     = self::init();
        $condition = self::formatCondition($condition , $db);
        $result = $db->collection($collectionName)->where($condition)->remove();
        return $result['deleted'] ?? 0;
    }

    /**
     * @param $collectionName
     * @param $condition
     * @param $data
     * @return int 返回受影响行数
     * @throws \TencentCloudBase\Utils\TcbException
     */
    static public function update($collectionName , $condition , $data):int
    {
        $db        = self::init();
        $condition = self::formatCondition($condition, $db);
        $result    = $db->collection($collectionName)->where($condition)->update($data);
        print_r(['update' => $result]);
        return $result['updated'] ?? 0;
    }

    /**
     * @param $collectionName
     * @param $id
     * @param $data
     * @return int 返回受影响行数
     * @throws \TencentCloudBase\Utils\TcbException
     */
    static public function updateById($collectionName , $id , $data):int
    {
        $db     = self::init();
        $result = $db->collection($collectionName)->doc($id)->update($data);
        print_r(['updateById' => $result]);
        return $result['updated'] ?? 0;
    }

    /**
     * @param $collectionName
     * @param $field
     * @param int $value
     * return int 受影响行数
     */
    static public function inc(string $collectionName , string $field , array $condition , int $value = 1):int
    {
        $db     = self::init();
        $command = $db->command;
        $condition = self::formatCondition($condition , $db);
        $result = $db->collection($collectionName)->where($condition)->update([$field => $command->inc($value)]);
        return $result['updated'] ?? 0;
    }

    private static function formatCondition(array $condition , $db):array
    {
        if(!$condition) return $condition;
        $command = $db->command;
        foreach ($condition as $key => $value)
        {
            if(!is_array($value)) continue;
            if(count($value)==1){
                $nValue=$value;
                $z=[];
                $i=0;
                while (true){
                    $isStop=false;
                    foreach ($nValue as $k=>$v){
                        if(count($v)==1){
                            array_push($z,$k);
                            $nValue=$v;
                            $i++;
                            break;
                        }else{
                            array_push($z,$k);
                            $operator   = self::$commandFunc[$v[0]];
                            $whereParam = $v[1];
                            $v = $command->{$operator}($whereParam);
                            $count      = count($z);
                            while ($count >0){
                                $k=array_pop($z);
                                $v=[$k=>$v];
                                $count--;
                            }
                            $condition[$key]=$v;
                            $isStop=true;
                        }
                    }
                    if($isStop){
                        break;
                    }
                }
            }elseif (count($value)==2){
                $operator   = self::$commandFunc[$value[0]];
                $whereParam = $value[1];
                $condition[$key] = $command->{$operator}($whereParam);
            }
        }
        print_r($condition);
        return $condition;
    }
}