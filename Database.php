<?php


namespace CodeMade\WuiBundle;


use CodeMade\WuiBundle\Pdo\Connection;
use stdClass;
use Symfony\Component\HttpKernel\KernelInterface;


class Database
{
    private $kernel;
    private $settings;

    protected $default_connection;

    /**
     * @var array have object connection
     */
    protected $connections;

    public $debug;


    public function __construct(KernelInterface $kernel, array $settings = [])
    {
        $this->settings = $settings;
        $this->kernel = $kernel;

        $this->debug = $this->kernel->isDebug() ? true : false;

        $this->default_connection = isset($this->settings['default_connection']) ? $this->settings['default_connection'] : false;
        $this->connections = new stdClass();
        $this->setConnection();

    }


    protected function setConnection()
    {
        if (!empty($this->settings['connections'])) {
            foreach ($this->settings['connections'] as $key => $connection) {
                $this->connections->{$key} = new Connection($connection);
                if (!$this->connections->{$key}) {
                    throw new \LogicException('Connection error.');
                }
            }
        }
    }

    /**
     * @param null $name
     * @return Connection
     */
    public function getConnection($name = null)
    {
        $name = !empty($name) ? $name : $this->default_connection;
        if (!is_object($this->connections->{$name})) {
            throw new \LogicException('Connection error.');
        }
        return $this->connections->{$name};
    }
}