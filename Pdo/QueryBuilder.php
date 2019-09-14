<?php


namespace CodeMade\WuiBundle\Pdo;


use CodeMade\WuiBundle\Database;

class QueryBuilder
{

    /* The query types. */
    public const SELECT = 0;
    public const DELETE = 1;
    public const UPDATE = 2;
    public const INSERT = 3;

    protected $sqlParts;

    /**
     * @var int query type
     */
    private $type = self::SELECT;

    protected $single = false;
    protected $query = null;

    protected $setCount = 0;

    protected $changeVar = [];


    protected $fromAlias = '';
    protected $useAlias = 0;
    protected $useAliasTable = 0;

    /**
     * @var array map key
     */
    protected $parameters = [];


    protected $repositoryTableMaps;

    /**
     * The index of the first result to retrieve.
     *
     * @var int
     */
    private $firstResult = 0;
    /**
     * The maximum number of results to retrieve.
     *
     * @var int|null
     */
    private $maxResults = null;


    /**
     * @var PdoQuery
     */
    protected $PdoQuery = null;

    public function __construct(PdoQuery $PdoQuery)
    {
        $this->PdoQuery = $PdoQuery;

    }

    public function createQueryBuilder($repositoryTableMaps = null)
    {

        $this->parameters = [];
        $this->single = false;
        $this->query = null;
        $this->firstResult = 0;
        $this->maxResults = null;
        $this->changeVar = [];
        $this->setCount = 0;
        $this->useAlias = $this->useAliasTable = 0;
        $this->repositoryTableMaps = $repositoryTableMaps;
        $this->fromAlias = '';
        $this->sqlParts = [
            'distinct' => false,
            'select'  => [],
            'from'    => [],
            'join'    => [],
            'set'     => [],
            'where'   => [],
            'groupBy' => [],
            'having'  => null,
            'orderBy' => []
        ];

        return $this;
    }

    /**
     * Adds a DISTINCT flag to this query.
     *
     * <code>
     *     $qb = $em->createQueryFactory()
     *         ->select('u')
     *         ->distinct()
     *         ->from('User', 'u');
     * </code>
     *
     * @param bool $flag
     *
     * @return self
     */
    public function distinct($flag = true)
    {
        $this->sqlParts['distinct'] = (bool) $flag;
        return $this;
    }

    public function select($select)
    {
        $this->type = self::SELECT;
        if (empty($select)) {
            return $this;
        }
        $this->sqlParts['select'] = [];
        $selects = is_array($select) ? $select : func_get_args();

        $this->sqlParts['select'] = array_merge($this->sqlParts['select'], $selects);

        return $this;
    }

    public function addSelect($select)
    {
        $this->type = self::SELECT;
        if (empty($select)) {
            return $this;
        }
        $selects = is_array($select) ? $select : func_get_args();
        $this->sqlParts['select'] = array_merge($this->sqlParts['select'], $selects);

        return $this;
    }

    /**
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->delete('User', 'u')
     *         ->where('u.id = :user_id')
     *         ->setParameter('user_id', 1);
     * </code>
     *
     * @param string $delete
     * @param string $alias
     *
     * @return self
     */
    public function delete($delete = null, $alias = null)
    {

        if ($this->type != self::DELETE) {
            $this->sqlParts['from'] = [];
        }

        $this->type = self::DELETE;
        if (! $delete) {
            return $this;
        }
        $this->useAliasTable++;
        if ($alias) {
            $this->useAlias++;
            $alias = ' AS `'.$alias.'`';
        }

        array_push($this->sqlParts['from'], '`'.$delete.'`'.$alias);
        return $this;
    }

    public function update($update = null, $alias = null)
    {
        if ($this->type != self::UPDATE) {
            $this->sqlParts['from'] = [];
        }

        $this->type = self::UPDATE;
        if (! $update) {
            return $this;
        }
        $this->useAliasTable++;
        if ($alias) {
            $this->useAlias++;
            $alias = ' AS `'.$alias.'`';
        }

        array_push($this->sqlParts['from'], '`'.$update.'`'.$alias);
        return $this;
    }

    public function insert($insert = null, $alias = null)
    {
        if ($this->type != self::INSERT) {
            $this->sqlParts['from'] = [];
        }
        $this->type = self::INSERT;
        if (! $insert) {
            return $this;
        }
        $this->useAliasTable++;
        if ($alias) {
            $this->useAlias++;
            $alias = ' AS `'.$alias.'`';
        }
        $this->sqlParts['from'] = [];
        array_push($this->sqlParts['from'], '`'.$insert.'`'.$alias);
        return $this;
    }

    public function set(array $set)
    {
        if (!is_array($set)) {
            return $this;
        }

        foreach ($set as $inKey => $inValue) {
            $this->setCount++;
            $name = '_v'.$this->setCount;
            $inKey = '`'.str_replace('.', '`.`', $inKey).'`';
            $this->sqlParts['set'][] = $inKey.' = :'.$name;
            $this->parameters[$name] = $inValue;
        }

        return $this;

    }

    public function from($from, $alias)
    {

        if (! $from) {
            return $this;
        }

        if ($alias) {
            $this->fromAlias = $alias;
            $alias = ' AS `'.$alias.'`';
        }
        $this->sqlParts['from'] = [];
        array_push($this->sqlParts['from'], '`'.$from.'`'.$alias);
        return $this;
    }

    public function addFrom($from = null, $alias = null)
    {
        if (! $from) {
            return $this;
        }

        if ($alias) {
            $alias = ' AS `'.$alias.'`';
        }

        array_push($this->sqlParts['from'], '`'.$from.'`'.$alias);
        return $this;
    }

    public function where($where)
    {
        if ($this->type == self::INSERT) {
            throw new \LogicException('WHERE not found in INSERT.');
        }
        if (!empty($this->sqlParts['where'])) {
            throw new \LogicException('WHERE used. Use addWhere() || orWhere().');
        }

        $this->sqlParts['where'] = [];
        if (is_array($where)) {
               return $this->_whereArray($where);
        }
            $where = $this->getParametersFromWhere($where);


        array_push($this->sqlParts['where'], $where);

        return $this;
    }

    public function andWhere($where)
    {
        if ($this->type == self::INSERT) {
            throw new \LogicException('WHERE not found in INSERT.');
        }

        if (is_array($where)) {
            return $this->_whereArray($where);
        }

        $where = $this->getParametersFromWhere($where);

        array_push($this->sqlParts['where'], $where);

        return $this;
    }

    public function orWhere($where)
    {
        if ($this->type == self::INSERT) {
            throw new \LogicException('WHERE not found in INSERT.');
        }

        if (is_array($where)) {
            return $this->_whereArray($where);
        }

        $where = $this->getParametersFromWhere($where);

        array_push($this->sqlParts['where'], '('.$where.')');

        return $this;
    }

    protected function _whereArray($where)
    {
        foreach ($where as $inKey => $inValue) {
            $this->setCount++;
            $name = '_v'.$this->setCount;
            $inKey = '`'.str_replace('.', '`.`', $inKey).'`';
            $this->sqlParts['where'][] = $inKey.' = :'.$name;
            $this->parameters[$name] = $inValue;
        }

        return $this;
    }


    public function orderBy($sort, $order = null)
    {
        if (is_array($sort)) {
            foreach ($sort as $key => $item) {
                $this->orderBy($key, $item);
            }
            return $this;
        }

        $order_sort = '';
        if (!empty($order) && in_array(mb_strtoupper($order), ['DESC', 'ASC'])) {
            $order_sort = ' '.mb_strtoupper($order);
        } elseif (!empty($order)) {
            throw new \LogicException('orderBy "'.$order . '" not found.');
        }


        $order = !empty($order) ? ' '.$order: '';
        array_push($this->sqlParts['orderBy'], $sort.$order_sort);

        return $this;
    }

    public function getOneOrReturnFalse()
    {
        $this->single = true;
        $this->setMaxResults(1);
        $this->getQuery();
        return $this->exec();
    }


    public function setPage($page, $maxResults)
    {

        $firstResult = ($page - 1) * $maxResults;
        $this->setMaxResults($maxResults);
        $this->setFirstResult($firstResult);
        return $this;
    }

    public function setMaxResults($limit)
    {
        $this->maxResults = $limit;
        return $this;
    }

    public function setFirstResult($offset)
    {
        $this->firstResult = $offset;
        return $this;
    }


    public function leftJoin($join, $alias, $conditionType = null, $condition = null)
    {
        $this->getJoinString('LEFT', $join, $alias, $conditionType, $condition);
        return $this;
    }

    public function rightJoin($join, $alias, $conditionType = null, $condition = null)
    {
        $this->getJoinString('RIGHT', $join, $alias, $conditionType, $condition);
        return $this;
    }

    public function innerJoin($join, $alias, $conditionType = null, $condition = null)
    {
        $this->getJoinString('INNER', $join, $alias, $conditionType, $condition);
        return $this;
    }


    protected function getJoinString($type, $join, $alias, $conditionType = null, $condition = null)
    {
        if ($this->type != self::SELECT) {
            throw new \LogicException('Join use only with SELECT.');
        }

        if (! $join) {
            return $this;
        }

        if (!empty($this->repositoryTableMaps[$join]['join'])) {

            $condition = '';
            if ($this->fromAlias != '') {
                $condition = '`'.$this->fromAlias.'`.';
            }

            $condition .= '`'.$this->repositoryTableMaps[$join]['join']['columnName'].'` = `'.$alias.'`.`'.$this->repositoryTableMaps[$join]['join']['referencedColumnName'].'`';

            $join = $this->repositoryTableMaps[$join]['join']['table'];
            $conditionType = 'ON';

        }


        $alias = ' AS `'.$alias.'`';

        $join = $type.' JOIN `'.$join.'`'.$alias;


        if (!empty($conditionType) && in_array(mb_strtoupper($conditionType), ['ON', 'USING']) && !empty($condition)) {
            $join .= ' '. mb_strtoupper($conditionType) . ' ';
            if (is_array($condition)) {
                $join .= implode(' AND ', $condition);
            } else {
                $join .= $condition;
            }
        }


        $this->sqlParts['join'][] = $join;
    }

    protected function getParametersFromWhere($where)
    {
        if (preg_match_all('/([a-zA-Z-_.0-9]+)\s+([=|>|>=|<|<=|!=|LIKE|IN]+)\s+[(+]?\s*+:([a-zA-Z-_0-9]+)\s*+[)+]?/iu', $where, $match)) {
            if (isset($match[1])) {
                foreach ($match[1] as $item)
                {
                    $old_item = $item;
                    $item = '`'.str_replace('.', '`.`', $item).'`';
                    $where = str_replace($old_item, $item, $where);
                }
            }
            if (isset($match[3])) {
                foreach ($match[3] as $item)
                {
                    $this->parameters[$item] = true;
                }
            }
        }

        return $where;
    }

    public function setParameter($name, $value)
    {
        if (!isset($this->parameters[$name])) {
            throw new \LogicException('Parameter "'.$name . '" not found.');
        }

        if ($this->parameters[$name] !== true) {
            throw new \LogicException('Parameter "'.$name . '" already exists.');
        }

        if (is_array($value)) {
            $change_var = '';
            foreach ($value as $i => $item)
            {
                $key = $name.$i;
                $change_var .= ':'.$key.',';
                $this->parameters[$key] = $item;
            }
            $change_var = rtrim($change_var,",");
            $this->changeVar[$name] = $change_var;
            $this->parameters[$name] = 'change';
        } else {
            $this->parameters[$name] = $value;
        }



        return $this;
    }



    public function getQuery() {


        if ($this->useAlias == 1 && $this->useAliasTable == 1) {
            throw new \LogicException('DELETE UPDATE INSERT one table not use Alias.');
        }

        switch ($this->type) {
            case self::DELETE:
                $sql = $this->getSQLForDelete();
                break;
            case self::UPDATE:
                $sql = $this->getSQLForUpdate();
                break;
            case self::INSERT:
                $sql = $this->getSQLForInsert();
                break;
            case self::SELECT:
            default:
            $sql = $this->getSQLForSelect();
                break;
        }


        if ($this->changeVar) {
            foreach ($this->changeVar as $key => $item) {
                if (isset($this->parameters[$key])) {
                    unset($this->parameters[$key]);
                }
                $sql = preg_replace('/[(+]\s*+:'.$key.'\s*+[)+]/', '('.$item.')', $sql);

            }
        }


        $key = array_search('green', $this->parameters);

        if (!empty($key)) {
            throw new \LogicException('Parameter "' . $key . '" not value.');
        }


        $this->query = $sql;
        return $this->query;
    }


    public function exec()
    {
        $this->getQuery();

        if (empty($this->query)) {
            throw new \LogicException('First do getQuery().');
        }

        $result = $this->PdoQuery->query($this->type, $this->query, $this->parameters);

        if ($this->single) {

            return isset($result[0]) ? $result[0] : false;
        }
        return isset($result[0]) ? $result : false;


    }


    /**
     * Gets a query part by its name.
     *
     * @param $queryPartName
     * @return mixed
     */
    public function getSQLPart($queryPartName)
    {
        return $this->sqlParts[$queryPartName];
    }
    /**
     * Gets all query parts.
     *
     * @return mixed[] $sqlParts
     *
     */
    public function getSQLParts()
    {
        return $this->sqlParts;
    }


    /**
     * @return string
     */
    public function getSQLForDelete()
    {
        return 'DELETE'
            . $this->getReducedSQLQueryPart('from', ['pre' => ' ', 'separator' => ', '])
            . $this->getReducedSQLQueryPart('where', ['pre' => ' WHERE ', 'separator' => ' AND '])
            . $this->getReducedSQLQueryPart('orderBy', ['pre' => ' ORDER BY ', 'separator' => ', ']);
    }
    /**
     * @return string
     */
    public function getSQLForUpdate()
    {
        return 'UPDATE'
            . $this->getReducedSQLQueryPart('from', ['pre' => ' ', 'separator' => ', '])
            . $this->getReducedSQLQueryPart('set', ['pre' => ' SET ', 'separator' => ', '])
            . $this->getReducedSQLQueryPart('where', ['pre' => ' WHERE ', 'separator' => ' AND '])
            . $this->getReducedSQLQueryPart('orderBy', ['pre' => ' ORDER BY ', 'separator' => ', ']);
    }
    /**
     * @return string
     */
    public function getSQLForSelect()
    {
        $SQL = 'SELECT'
            . ($this->sqlParts['distinct']===true ? ' DISTINCT' : '')
            . $this->getReducedSQLQueryPart('select', ['pre' => ' ', 'separator' => ', ']);

        $SQL .=
            ' FROM' . $this->getReducedSQLQueryPart('from', ['pre' => ' ', 'separator' => ', '])
            . $this->getReducedSQLQueryPart('join', ['pre' => ' ', 'separator' => ' '])
            . $this->getReducedSQLQueryPart('where', ['pre' => ' WHERE ', 'separator' => ' AND '])
            . $this->getReducedSQLQueryPart('groupBy', ['pre' => ' GROUP BY ', 'separator' => ', '])
            . $this->getReducedSQLQueryPart('having', ['pre' => ' HAVING '])
            . $this->getReducedSQLQueryPart('orderBy', ['pre' => ' ORDER BY ', 'separator' => ', ']);

        if (!empty($this->maxResults) && empty($this->firstResult)){
            $SQL .= ' LIMIT '.$this->maxResults;
        }
        if (!empty($this->maxResults) && !empty($this->firstResult)){
            $SQL .= ' LIMIT '.$this->firstResult.','.$this->maxResults;
        }

        return $SQL;
    }
    /**
     * @return string
     */
    public function getSQLForInsert()
    {
        return 'INSERT INTO'
            . $this->getReducedSQLQueryPart('from', ['pre' => ' ', 'separator' => ', '])
            . $this->getReducedSQLQueryPart('set', ['pre' => ' SET ', 'separator' => ', ']);
    }


    /**
     * @param string  $queryPartName
     * @param mixed[] $options
     *
     * @return string
     */
    private function getReducedSQLQueryPart($queryPartName, $options = [])
    {
        $queryPart = $this->getSQLPart($queryPartName);
        if (empty($queryPart)) {
            return $options['empty'] ?? '';
        }
        return ($options['pre'] ?? '')
            . (is_array($queryPart) ? implode($options['separator'], $queryPart) : $queryPart)
            . ($options['post'] ?? '');
    }


}