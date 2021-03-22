<?php
namespace Plinct\PDO;

use PDO;
use PDOException;

class PDOConnect {
    private static ?object $PDOConnect;
    private static string $DRIVER;
    private static string $HOST;
    private static string $DBNAME;
    private static string $USER_PUBLIC;
    private static string $PASSWORD_PUBLIC;
    private static string $USERNAME_ADMIN;
    private static string $EMAIL_ADMIN;
    private static string $PASSWORD_ADMIN;
    private static object $ERROR;

    /**
     * @param $driver
     * @param $host
     * @param $dbname
     * @param $username
     * @param $password
     * @param array $options
     * @return array[]|PDO
     */
    public static function connect($driver, $host, $dbname, $username, $password, $options = []) {
        self::$DRIVER = $driver;
        self::$HOST = $host;
        self::$DBNAME = $dbname;
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

    public static function disconnect() {
        self::$PDOConnect = null;
    }

    public static function getError(): ?array {
        if (self::$ERROR) {
            return [ "error" => [
                "message" => self::$ERROR->getMessage(),
                "code" => self::$ERROR->getCode()
            ]];
        }
        return null;
    }

    public static function getPDOConnect(): ?object {
        return self::$PDOConnect;
    }

    public static function getDrive(): string {
        return self::$DRIVER;
    }

    public static function getHost(): string {
        return self::$HOST;
    }

    public static function getDbname(): string {
        return self::$DBNAME;
    }

    public static function getUsernameAdmin(): string {
        return self::$USERNAME_ADMIN;
    }

    public static function getEmailAdmin(): string {
        return self::$EMAIL_ADMIN;
    }

    public static function getPasswordAdmin(): string {
        return self::$PASSWORD_ADMIN;
    }

    public static function setUserPublic($userPublic) {
        self::$USER_PUBLIC = $userPublic;
    }

    public static function setPasswordPublic($passwordPublic) {
        self::$PASSWORD_PUBLIC = $passwordPublic;
    }

    public static function setUsernameAdmin($usernameAdmin) {
        self::$USERNAME_ADMIN = $usernameAdmin;
    }

    public static function setEmailAdmin($emailAdmin) {
        self::$EMAIL_ADMIN = $emailAdmin;
    }

    public static function setPasswordAdmin($passwordAdmin) {
        self::$PASSWORD_ADMIN = $passwordAdmin;
    }

    public static function reconnectToAdmin() {
        self::disconnect();
        self::connect(self::$DRIVER, self::$HOST, self::$DBNAME, self::$USERNAME_ADMIN ?? self::$USER_PUBLIC, self::$PASSWORD_ADMIN ?? self::$PASSWORD_PUBLIC);
    }

    public static function reconnectToPublic() {
        self::disconnect();
        self::connect(self::$DRIVER, self::$HOST, self::$DBNAME, self::$USER_PUBLIC, self::$PASSWORD_PUBLIC);
    }
    /**
     * @param $query
     * @param null $args
     * @return array[]
     */
    public static function run($query, $args = NULL): array {
        $errorInfo = null;
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
            } elseif ($errorInfo !== '0000') {
                return [ "error" => [
                    "message" => $errorInfo[2],
                    "code" => $errorInfo[1],
                    "query" => $query
                ] ];
            } else {
                return [ "error" => [
                    "message" => $e->getMessage(),
                    "code" => $e->getCode()
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
