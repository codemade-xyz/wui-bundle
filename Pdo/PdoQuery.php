<?php


namespace CodeMade\WuiBundle\Pdo;


use PDO;
use CodeMade\WuiBundle\Database;

class PdoQuery
{

    /**
     * @var Connection
     */
    public $connection = null;

    protected $connection_name;

    protected $debug;

    protected $prefix;

    public function __construct(Database $database, $connection_name)
    {
        $this->connection_name = $connection_name;
        $this->connection = $database->getConnection($connection_name);
        $this->debug = !$database->debug ? false : true;
    }

    public function get($table, $join = null, $columns = null, $where = null)
    {
        $start = microtime(true);
        $result = $this->connection->get($table, $join, $columns, $where);

        if ($this->debug)
        {
            $this->addLog($start);
        }

        return $result;
    }

    public function selectAsObject($table, $join = null, $columns = null, $where = null)
    {
        $start = microtime(true);

        $result = (object)$this->connection->get($table, $join, $columns, $where);

        if ($this->debug)
        {
            $this->addLog($start);
        }

        return $result;

    }

    public function select($table, $join = null, $columns = null, $where = null)
    {
        $start = microtime(true);

        $result = $this->connection->select($table, $join, $columns, $where);

        if ($this->debug)
        {
            $this->addLog($start);
        }

        return $result;

    }

    public function count($table, $join = null, $columns = null, $where = null)
    {
        $start = microtime(true);
        $result = $this->connection->count($table, $join, $columns, $where);
        if ($this->debug)
        {
            $this->addLog($start);
        }

        return $result;

    }

    public function insert($table, $data)
    {
        $start = microtime(true);

        $this->connection->insert($table, $data);
        $result = $this->connection->id();

        if ($this->debug)
        {
            $this->addLog($start);
        }

        return $result;

    }

    public function update($table, $data, $where = null)
    {
        $start = microtime(true);

        $result = $this->connection->update($table, $data, $where);

        if ($this->debug)
        {
            $this->addLog($start);
        }

        return $result;

    }

    public function delete($table, $where = null)
    {
        $start = microtime(true);

        if ($where != null && is_array($where)) {
            $result = $this->connection->delete($table, $where);
            if ($this->debug)
            {
                $this->addLog($start);
            }

            return $result;
        } else {
            return false;
        }

    }

    protected function addLog($start)
    {


            $add_error = '';
            if (is_array($this->connection->error()) && !empty($this->connection->error()[2]) && gettype($this->connection->error()[2]) === 'string') {
                $add_error = $this->connection->error()[2];
                Logger::addError($add_error);
            }

            $finish = $this->getExecTime($start);
            Logger::addTimeAll($finish);
            Logger::addLog([
                'time' => $finish,
                'query' => $this->connection->last(),
                'error' => $add_error,
                'connection' => $this->connection_name
            ]);



    }

    public function query($type, $query, $map = [], $fetch = true)
    {
        $start = microtime(true);

        $result = false;

        $raw = $this->raw($query, $map);
        $query = $this->buildRaw($raw, $map);

        $statement = $this->connection->pdo->prepare($query);
        if ($statement)
        {
            foreach ($map as $key => $value)
            {
                $statement->bindValue(':'.$key, $value[ 0 ], $value[ 1 ]);
            }
            $statement->execute();

        }

        if ($this->debug)
        {

            $add_error = '';
            if (is_array($statement->errorInfo()) && !empty($statement->errorInfo()[2]) && gettype($statement->errorInfo()[2]) === 'string') {
                $add_error = $statement->errorInfo()[2];
                Logger::addError($add_error);
            }

            $finish = $this->getExecTime($start);
            Logger::addTimeAll($finish);
            Logger::addLog([
                'time' => $finish,
                'query' => $statement->queryString,
                'map' => $map,
                'error' => $add_error,
                'connection' => $this->connection_name
            ]);


        }

        if ($type == 3) {
            return $this->connection->pdo->lastInsertId();
        }

        if ($fetch) {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = $statement;
        }

        return $result;

    }

    protected function getExecTime($timeStart = null) {

        $durationInMilliseconds = (microtime(true) - $timeStart) * 1000;
        return number_format($durationInMilliseconds, 2, '.', '');
    }


    public static function raw($string, $map = [])
    {
        $raw = new Raw();
        $raw->map = $map;
        $raw->value = $string;
        return $raw;
    }


    protected function buildRaw($raw, &$map)
    {
        if (!$this->isRaw($raw))
        {
            return false;
        }

        $query = $raw->value;
        $raw_map = $raw->map;

        if (!empty($raw_map))
        {
            foreach ($raw_map as $key => $value)
            {
                $map[ $key ] = $this->typeMap($value, gettype($value));
            }
        }
        return $query;
    }

    protected function isRaw($object)
    {
        return $object instanceof Raw;
    }

    protected function typeMap($value, $type)
    {
        $map = [
            'NULL' => PDO::PARAM_NULL,
            'integer' => PDO::PARAM_INT,
            'double' => PDO::PARAM_STR,
            'boolean' => PDO::PARAM_BOOL,
            'string' => PDO::PARAM_STR,
            'object' => PDO::PARAM_STR,
            'resource' => PDO::PARAM_LOB
        ];
        if ($type === 'boolean')
        {
            $value = ($value ? '1' : '0');
        }
        elseif ($type === 'NULL')
        {
            $value = null;
        }
        return [$value, $map[ $type ]];
    }

}