<?php

declare(strict_types=1);

namespace Plinct\PDO;

use PDO;
use PDOException;

class PDOConnect
{
    /**
     * @var
     */
    private static $PDOConnect;
    /**
     * @var
     */
    private static  $DRIVER;
    /**
     * @var
     */
    private static  $HOST;
    /**
     * @var
     */
    private static  $DBNAME;
    /**
     * @var
     */
    private static  $USERNAME;
    /**
     * @var
     */
    private static  $PASSWORD;
    /**
     * @var
     */
    private static  $ERROR;

    /**
     * @param $driver
     * @param $host
     * @param $dbname
     * @param $username
     * @param $password
     * @param array $options
     * @return array[]|PDO
     */
    public static function connect($driver, $host, $dbname, $username, $password, array $options = [])
    {
        self::$DRIVER = $driver;
        self::$HOST = $host;
        self::$DBNAME = $dbname;
        self::$USERNAME = $username;
        self::$PASSWORD = $password;
        $default_options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true
        ];
        $options = array_replace($default_options, $options);
        $dsn = $driver . ":host=" . $host . ";dbname=" . $dbname;
        try {
            $PDOConnect = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            self::$ERROR = $e;
            $PDOConnect = self::getError();
        } finally {
            self::$PDOConnect = $PDOConnect;
        }
        return self::$PDOConnect;
    }

    /**
     *
     */
    public static function disconnect()
    {
        self::$PDOConnect = null;
    }

    /**
     * @return array[]|null
     */
    public static function getError(): ?array
    {
        if (self::$ERROR) {
            return [ "error" => [
                "message" => self::$ERROR->getMessage(),
                "code" => self::$ERROR->getCode()
            ]];
        }
        return null;
    }

    /**
     * @return object|null
     */
    public static function getPDOConnect(): ?object
    {
        return self::$PDOConnect;
    }

    /**
     * @return string
     */
    public static function getDrive(): string
    {
        return self::$DRIVER;
    }

    /**
     * @return string
     */
    public static function getHost(): string
    {
        return self::$HOST;
    }

    /**
     * @return string
     */
    public static function getDbname(): string
    {
        return self::$DBNAME;
    }

    /**
     * @param $username
     */
    public static function setUsername($username)
    {
        self::$USERNAME = $username;
    }

    /**
     * @param $password
     */
    public static function setPassword($password)
    {
        self::$PASSWORD = $password;
    }

    /**
     * @param null $username
     * @param null $password
     */
    public static function reconnect($username = null, $password = null)
    {
        self::disconnect();
        self::connect(self::$DRIVER, self::$HOST, self::$DBNAME, $username ?? self::$USERNAME, $password ?? self::$PASSWORD);
    }

    /**
     * @param $query
     * @param null $args
     * @return array[]
     */
    public static function run($query, $args = NULL): array
    {
        $connect = self::$PDOConnect;
        try {
            if ($connect && !isset($connect->error)) {
                $q = $connect->prepare($query);
                $q->setFetchMode(PDO::FETCH_ASSOC);
                $q->execute($args);
                $errorInfo = $q->errorInfo();
                if ($errorInfo[0] == "0000") {
                    return $q->fetchAll();
                } else {
                    throw new PDOException();
                }
            } else {
                throw new PDOException();
            }
        } catch (PDOException $e) {
            if(isset($connect->error)) {
                return (array) $connect;
            } else {
                return [ "error" => [
                    "message" => $e->getMessage(),
                    "code" => $e->getCode(),
                    "query" => $query
                ] ];
            }
        }
    }

    /**
     * LAST INSERT ID
     */
    public static function lastInsertId(): string {
        $query = "SELECT LAST_INSERT_ID() AS id;";
        $return = self::run($query);
        return $return[0]['id'];
    }
}
