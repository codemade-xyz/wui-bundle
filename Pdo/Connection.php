<?php


namespace CodeMade\WuiBundle\Pdo;


use PDO;
use Exception;
use PDOException;
use InvalidArgumentException;


class Connection
{

    /**
     * @var PDO
     */
    public $pdo;
    public $debug;

    public function __construct($settings)
    {

        if (isset($settings[ 'driver' ]))
        {
            $this->type = strtolower($settings[ 'driver' ]);
        }

        if (isset($settings[ 'debug' ]))
        {
            $this->debug = $settings[ 'debug' ];
        }

        $option = isset($settings[ 'option' ]) ? $settings[ 'option' ] : [];

        $commands = (isset($settings[ 'command' ]) && is_array($settings[ 'command' ])) ? $settings[ 'command' ] : [];

        switch ($this->type)
        {
            case 'pdo_mysql':
                // Make MySQL using standard quoted identifier
                $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                break;
            case 'pdo_mssql':
                // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
                $commands[] = 'SET QUOTED_IDENTIFIER ON';
                // Make ANSI_NULLS is ON for NULL value
                $commands[] = 'SET ANSI_NULLS ON';
                break;
        }


            if (
                isset($settings[ 'port' ]) &&
                is_int($settings[ 'port' ] * 1)
            )
            {
                $port = $settings[ 'port' ];
            }
            $is_port = isset($port);
            switch ($this->type)
            {
                case 'pdo_mysql':
                    $attr = [
                        'driver' => 'mysql',
                        'dbname' => $settings[ 'dbname' ]
                    ];
                    if (isset($settings[ 'socket' ]))
                    {
                        $attr[ 'unix_socket' ] = $settings[ 'socket' ];
                    }
                    else
                    {
                        $attr[ 'host' ] = $settings[ 'host' ];
                        if ($is_port)
                        {
                            $attr[ 'port' ] = $port;
                        }
                    }
                    break;
                case 'pdo_pgsql':
                    $attr = [
                        'driver' => 'pgsql',
                        'host' => $settings[ 'host' ],
                        'dbname' => $settings[ 'dbname' ]
                    ];
                    if ($is_port)
                    {
                        $attr[ 'port' ] = $port;
                    }
                    break;
            }

        if (!isset($attr))
        {
            throw new InvalidArgumentException('Incorrect connection options');
        }
        $driver = $attr[ 'driver' ];
        if (!in_array($driver, PDO::getAvailableDrivers()))
        {
            throw new InvalidArgumentException("Unsupported PDO driver: {$driver}");
        }
        unset($attr[ 'driver' ]);
        $stack = [];
        foreach ($attr as $key => $value)
        {
            $stack[] = is_int($key) ? $value : $key . '=' . $value;
        }
        $dsn = $driver . ':' . implode(';', $stack);
        if (
            in_array($this->type, ['pdo_mysql', 'pdo_pgsql']) &&
            isset($settings[ 'charset' ])
        )
        {
            $commands[] = "SET NAMES '{$settings[ 'charset' ]}'" . (
                $this->type === 'pdo_mysql' && isset($settings[ 'collation' ]) ?
                    " COLLATE '{$settings[ 'collation' ]}'" : ''
                );
        }

        try {
            $this->pdo = new PDO(
                $dsn,
                isset($settings[ 'user' ]) ? $settings[ 'user' ] : null,
                isset($settings[ 'password' ]) ? $settings[ 'password' ] : null,
                $option
            );
            foreach ($commands as $value)
            {
                $this->pdo->exec($value);
            }

        }
        catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
        
    }

}