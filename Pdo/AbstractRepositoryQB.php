<?php
namespace CodeMade\WuiBundle\Pdo;

use CodeMade\WuiBundle\Database;

class AbstractRepository
{
    protected $tableName = null;
    protected $tableMap = [
        'connection' => null,
        'id' => 'id'
    ];

    /**
     * @var QueryBuilder
     */
    protected $qb = null;

    /**
     * @var PdoQuery
     */
    protected $db = null;

    /**
     * PdoAbstractRepository constructor.
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $connection_name = isset($this->tableMap['connection']) ? $this->tableMap['connection'] : null;
        $this->db = new PdoQuery($database, $connection_name);
        $this->tableName = $this->getTable();
        $this->qb = new QueryBuilder($this->db);
    }

    public function find($id)
    {
        return $this->createQueryBuilder()
            ->where($this->tableMap['id'].' = :id')
            ->setParameter('id', $id)
            ->exec();
    }

    public function findOne($id)
    {
        return $this->createQueryBuilder()
            ->where($this->tableMap['id'].' = :id')
            ->setParameter('id', $id)
            ->getOneOrReturnFalse();
    }

    public function findBy($condition = [], $order = [])
    {
        return $this->createQueryBuilder()
            ->where($condition)
            ->orderBy($order)
            ->exec();

    }

    public function findOneBy($condition = [], $order = [])
    {
        return $this->createQueryBuilder()
            ->where($condition)
            ->orderBy($order)
            ->getOneOrReturnFalse();
    }

    public function update($data = [], $condition = [])
    {
        if (empty($condition)) {
            return false;
        }
        return $this->createQueryBuilder()
            ->update($this->tableName)
            ->set($data)
            ->where($condition)
            ->exec();
    }

    public function delete($condition = [])
    {
        if (empty($condition)) {
            return false;
        }
        return $this->createQueryBuilder()
            ->delete($this->tableName)
            ->where($condition)
            ->exec();
    }

    public function insert($data = [])
    {
        return $this->createQueryBuilder()
            ->insert($this->tableName)
            ->set($data)
            ->exec();
    }


    protected function setTableMaps($map)
    {
        return $this->tableMap = $map;
    }

    protected function setTableMap($key, $value)
    {
        return $this->tableMap[$key] = $value;
    }

    protected function getTableMap($key)
    {
        return isset($this->tableMap[$key]) ? $this->tableMap[$key] : false;
    }

    /**
     * @param $alias
     * @return QueryBuilder
     */
    public function createQueryBuilder($alias = null)
    {
        $alias_select = !empty($alias) ? $alias.'.' : null;
        $alias = !empty($alias) ? $alias : null;
        return $this->qb->createQueryBuilder($this->tableMap)
            ->select($alias_select.'*')
            ->from($this->tableName, $alias);
    }

    protected function getName()
    {
        return basename(str_replace('\\', '/', get_class($this)));
    }

    protected function getTable()
    {
        $res = array();
        preg_match_all('/[A-Z]*?[^A-Z]*?/U', $this->getName(), $matches);
        if (isset($matches[0]) && is_array($matches[0])) {
            return str_replace('_repository', '', mb_strtolower(implode('_', array_filter($matches[0]))));
        }
        return null;
    }
}