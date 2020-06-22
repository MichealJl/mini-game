<?php

namespace MiniGame\Database;

use MiniGame\Database\TcbDataBase;

class Base
{
    protected $original   = [];
    protected $condition  = [];
    protected $orderBy    = [];
    /**
     * 加入用户
     * @param $data
     */
    private function _create($data)
    {
        $attributes = $this->setPlayerProperties($data);
        $attributes['created_at'] = date('Y-m-d H:i:s');
        $attributes['updated_at'] = date('Y-m-d H:i:s');
        TcbDataBase::add($this->table, $attributes);
        return $this;
    }

    /**
     *根据条件查询用户
     * @return array
     * @throws \TencentCloudBase\Utils\TcbException
     */
    private function _get()
    {
        $condition       = $this->condition;
        $list            = TcbDataBase::get($this->table, $condition , $this->orderBy);
        $this->condition = [];
        $this->orderBy   = [];
        return array_map(function ($item){
            $obj = new static();
            $obj->original = $item;
            $obj->attributes = $item;
            return $obj;
        }, $list);
    }

    /**
     *根据条件查询条数
     * @return array
     * @throws \TencentCloudBase\Utils\TcbException
     */
    private function _count()
    {
        $condition = $this->condition;
        $count      = TcbDataBase::count($this->table , $condition);
        $this->condition = [];
        return $count;
    }

    /**
     *获取满足条件的第一个元素
     */
    private function _first()
    {
        $condition       = $this->condition;
        $list            = TcbDataBase::get($this->table, $condition);
        $this->condition = [];
        if (!$list) return null;
        $first = $list[0];
        $this->setPlayerProperties($first);
        return $this;
    }

    /**
     * 根据id查找单个元素
     * @param $id
     * @return $this
     * @throws \TencentCloudBase\Utils\TcbException
     */
    private function _find($id)
    {
        $find = TcbDataBase::find($this->table , $id);
        $this->setPlayerProperties($find);
        return $this;
    }

    /**
     * 单个更新
     * @return $this
     * @throws \TencentCloudBase\Utils\TcbException
     */
    private function _save()
    {
        $currProperties = $this->attributes;
        if (!$this->original || !$currProperties) return $this;
        $modifiedProperty = $this->modifiedProperty();
        if (!$modifiedProperty) return $this;
        $modifiedProperty['updated_at'] = date('Y-m-d H:i:s');
        TcbDataBase::updateById($this->table , $this->_id , $modifiedProperty);
        $this->original = $this->attributes;
        return $this;
    }

    private function modifiedProperty()
    {
        $currProperties = $this->attributes;
        $original       = $this->original;
        $diffProperty   = [];
        #从原有属性中判断相同的key值是否相同
        foreach ($original as $key => $value)
        {
            if (($currProperties[$key] ?? -99999) != $value && $key != '_id') $diffProperty[$key] = $currProperties[$key];
        }
        return $diffProperty;
    }

    /**
     * 批量更新
     * @param array $data
     * @return int 受影响行数
     * @throws \TencentCloudBase\Utils\TcbException
     */
    private function _update(array $data): int
    {
        $condition = $this->condition;
        if (!$condition) return 0;
        $column = TcbDataBase::update($this->table, $condition, $data);
        return $column;
    }

    private function _delete()
    {
        $columns = TcbDataBase::removeById($this->table , $this->_id);
        return $columns;
    }

    private function _where(string $key, $operation, $value = '', $recursion = false)
    {
        if ($value === '') {
            $value     = $operation;
            $operation = '=';
        }
        if (is_array($value)) {
            if (count($value) == 3) {
                list($nKey, $nOperation, $nValue) = $value;
            } else {
                list($nKey, $nOperation) = $value;
                $nValue = '';
            }
            $base = $this->_where($nKey, $nOperation, $nValue, true);
            if ($recursion) {
                return [$key => $base];
            } else {
                $this->condition[$key] = $base;
            }
        } else {
            if ($recursion) {
                return [$key => [$operation, $value]];
            }
            $this->condition[$key] = [$operation, $value];
        }
        return $this;
    }

    private function _incr($field , $value = 1)
    {
        $result = TcbDataBase::inc($this->table , $field , $this->condition , $value);
        $this->condition = [];
        return $result;
    }

    private function _orderBy($field, $orderType = 'asc')
    {
        $this->orderBy = [$field, $orderType];
        return $this;
    }

    private function setPlayerProperties($item)
    {
        foreach ($item as $key => $value) if (in_array($key, $this->attributes) || $key == '_id') $this->attributes[$key] = $value;
        $this->original = $this->attributes;
        return $this->original;
    }

    static public function getTableName()
    {
        return (new static())->table;
    }

    static public function __callStatic($name, $args)
    {
        $static = new static();
        return $static->callToUndefinedFunc($name, $args);
    }

    public function __call($name, $args)
    {
        return $this->callToUndefinedFunc($name, $args);
    }

    private function callToUndefinedFunc($name, $args)
    {
        $fullName = '_' . $name;
        if (!method_exists($this, $fullName)) return null;
        return $this->{$fullName}(...$args);
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->attributes)) $this->attributes[$name] = $value;
        return $this;
    }

    public function __get($name)
    {
        if($name == 'originalArr') return $this->original;
        return $this->attributes[$name] ?? null;
    }
}