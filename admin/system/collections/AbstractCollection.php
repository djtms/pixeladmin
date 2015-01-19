<?php namespace com\admin\system\collecions;

use com\admin\system\objects\AbstractObject;

abstract class AbstractCollection {
    private $object;
    private $table;

    function __construct(AbstractObject $object){
        $this->object = $object;
        $this->table = $object->getTable();
    }

    function listCollection(AbstractObject $where, AbstractObject $exclude, $limit = -1, $offset=0){
        $variables = array();
        $stringArr = array();

        $query = "SELECT * FROM {$this->table} ";

        if(($where !== null) && sizeof($where->toArray()) > 0){
            foreach($where->toArray() as $key=>$val){
                $stringArr[] = "{$key}=? ";
                $variables[] = $val;
            }
        }

        if(($exclude !== null) && sizeof($exclude->toArray()) > 0){
            foreach($exclude->toArray() as $key=>$val){
                $stringArr[] = "{$key}!=? ";
                $variables[] = $val;
            }
        }

        if(sizeof($stringArr) > 0){
            $query .= "WHERE " . join(" AND ", $stringArr) . " ";
        }

        if($limit > 0){
            $query .= "LIMIT {$offset},{$limit}";
        }

        if($data = $this->get_rows($query, $variables)){
            return ${get_class($this->object)}::convertToObjectCollection($data);
        }
        else{
            return false;
        }
    }

    function selectObject(AbstractObject $where, AbstractObject $exclude = null){
        $variables = array();
        $stringArr = array();

        $query = "SELECT * FROM {$this->table} ";

        if(sizeof($where->toArray()) <= 0){
            return false;
        }

        if(($where !== null) && sizeof($where->toArray()) > 0){
            foreach($where->toArray() as $key=>$val){
                $stringArr[] = "{$key}=? ";
                $variables[] = $val;
            }
        }

        if(($exclude !== null) && sizeof($exclude->toArray()) > 0){
            foreach($exclude->toArray() as $key=>$val){
                $stringArr[] = "{$key}!=? ";
                $variables[] = $val;
            }
        }

        if(sizeof($stringArr) > 0){
            $query .= "WHERE " . join(" AND ", $stringArr) . " ";
        }

        if($data = $this->get_row($query, $variables)){
            return new ${get_class($this->object)}($data);
        }
        else{
            return false;
        }
    }
}