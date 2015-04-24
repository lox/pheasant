<?php

namespace Pheasant\Database\Mysqli;

use Pheasant\Database\Dsn;
use Pheasant\Database\FilterChain;
use Pheasant\Database\MysqlPlatform;

/**
 * A connection to a MySql database
 */
class Connection
{
    private
        $_dsn,
        $_link,
        $_charset,
        $_filter,
        $_sequencePool,
        $_strict,
        $_selectedDatabase,
        $_debug=false
        ;

    public static
        $counter=0,
        $timer=0
        ;

    /**
     * Constructor
     * @param string a database uri
     */
    public function __construct(Dsn $dsn)
    {
        $this->_dsn = $dsn;
        $this->_filter = new FilterChain();
        $this->_charset = isset($this->_dsn->params['charset']) ?
            $this->_dsn->params['charset'] : 'utf8';
        $this->_strict = isset($this->_dsn->params['strict']) ?
            $this->_dsn->params['strict'] : false;

        if(!empty($this->_dsn->database))
            $this->_selectedDatabase = $this->_dsn->database;

        $this->_debug = getenv('PHEASANT_DEBUG');
    }

    /**
     * Selects a particular database
     * @chainable
     */
    public function selectDatabase($database)
    {
        $mysqli = $this->_mysqli();

        if(!$mysqli->select_db($database))
            throw new Exception($mysqli->error, $mysqli->errno);

        $this->_selectedDatabase = $database;

        return $this;
    }

    /**
     * Forces a connection, re-connects if already connected
     * @chainable
     */
    public function connect()
    {
        unset($this->_link);
        $this->_mysqli();

        return $this;
    }

    /**
     * Closes a connection
     * @chainable
     */
    public function close()
    {
        if(isset($this->_link))
            $this->_link->close();

        if(isset($this->_sequencePool))
            $this->_sequencePool->close();

        return $this;
    }

    /**
     * The charset used by the database connection
     * @return string
     */
    public function charset()
    {
        return $this->_charset;
    }

    /**
     * Lazily creates the internal mysqli object
     * @return MySQLi
     */
    private function _mysqli()
    {
        if (!isset($this->_link)) {
            mysqli_report(MYSQLI_REPORT_OFF);

            if(!$this->_link = mysqli_init())
                throw new Exception("Mysql initialization failed");

            $sqlMode = $this->_strict ? 'TRADITIONAL' : '';
            $this->_link->options(MYSQLI_INIT_COMMAND, "SET SESSION sql_mode = '{$sqlMode}'");
            $this->_link->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

            @$this->_link->real_connect(
                $this->_dsn->host, $this->_dsn->user, $this->_dsn->pass,
                $this->_dsn->database, $this->_dsn->port
            );

            if ($this->_link->connect_error)
                throw new Exception("Failed to connect to mysql: {$this->_link->connect_error}", $this->_link->connect_errno);

            if (!$this->_link->set_charset("utf8"))
                throw new Exception(sprintf("Error setting character to %s: %s", $this->_charset, $this->_link->error));
        }

        return $this->_link;
    }

    /**
     * Executes a statement
     * @return ResultSet
     */
    public function execute($sql, $params=array())
    {
        if(!is_array($params))
            $params = array_slice(func_get_args(),1);

        $mysqli = $this->_mysqli();
        $debug = $this->_debug;
        $sql = count($params) ? $this->binder()->bind($sql, $params) : $sql;

        // delegate execution to the filter chain
        return $this->_filter->execute($sql, function($sql) use ($mysqli, $debug) {
            \Pheasant\Database\Mysqli\Connection::$counter++;

            if($debug) {
                $timer = microtime(true);
            }

            $r = $mysqli->query($sql, MYSQLI_STORE_RESULT);

            if($debug) {
                \Pheasant\Database\Mysqli\Connection::$timer += microtime(true)-$timer;
            }

            if ($debug) {
                printf("<pre>Pheasant executed <code>%s</code> on thread #%d in %.2fms, returned %d rows</pre>\n\n",
                    $sql, $mysqli->thread_id, (microtime(true)-$timer)*1000, is_object($r) ? $r->num_rows : 0);
            }

            if ($mysqli->error) {
                if ($mysqli->errno === 1213 || $mysqli->errno === 1479) {
                    throw new DeadlockException($mysqli->error, $mysqli->errno);
                } else {
                    throw new Exception($mysqli->error, $mysqli->errno);
                }
            }

            return new ResultSet($mysqli, $r === true ? false : $r);
        });
    }

    /**
     * @return Transaction
     */
    public function transaction($callback=null)
    {
        $transaction = new Transaction($this);

        // optionally add a callback and any arguments
        if (func_num_args()) {
            call_user_func_array(array($transaction,'callback'),
                func_get_args());
        }

        return $transaction;
    }

    /**
     * @return Binder
     */
    public function binder()
    {
        return new Binder($this->_mysqli());
    }

    /**
     * @return Table
     */
    public function table($name)
    {
        $tablename = new TableName($name);
        if (is_null($tablename->database))
            $tablename->database = $this->selectedDatabase();

        return new Table($tablename, $this);
    }

    /**
     * @return SequencePool
     */
    public function sequencePool()
    {
        if (!isset($this->_sequencePool)) {
            // use a seperate connection, ensures transaction rollback
            // doesn't clobber sequences
            $this->_sequencePool = new SequencePool(new self($this->_dsn));
        }

        return $this->_sequencePool;
    }

    /**
     * Returns a platform object representing the database type connected to
     */
    public function platform()
    {
        return new MysqlPlatform();
    }

    /**
     * Returns the internal filter chain
     * @return FilterChain
     */
    public function filterChain()
    {
        return $this->_filter;
    }

    /**
     * Returns the selected database
     * @return string
     */
    public function selectedDatabase()
    {
        return $this->_selectedDatabase;
    }
}
