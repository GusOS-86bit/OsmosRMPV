<?php

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $db = "osmos_db";

    // one connection per page load
    private static $connection = null;

    function connect()
    {
        if (self::$connection === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            try {
                $connection = mysqli_connect($this->host, $this->username, $this->password, $this->db);
            } catch (mysqli_sql_exception $e) {
                error_log("Osmos DB connection failed: " . $e->getMessage());
                die("Could not connect to the database.");
            }

            mysqli_set_charset($connection, "utf8mb4");

            // keep mysql on the same timezone as php
            mysqli_query($connection, "SET time_zone = '" . date('P') . "'");

            self::$connection = $connection;
        }

        return self::$connection;
    }

    // false if the query fails or finds nothing
    function read($query)
    {
        $conn = $this->connect();

        try {
            $result = mysqli_query($conn, $query);
        } catch (mysqli_sql_exception $e) {
            error_log("Osmos DB read error: " . $e->getMessage() . " | Query: " . $query);
            return false;
        }

        if (!$result) {
            return false;
        }

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        return count($data) > 0 ? $data : false;
    }

    function save($query)
    {
        $conn = $this->connect();

        try {
            $result = mysqli_query($conn, $query);
        } catch (mysqli_sql_exception $e) {
            error_log("Osmos DB save error: " . $e->getMessage() . " | Query: " . $query);
            return false;
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    // only safe inside quotes in a query
    function escape($value)
    {
        if (is_array($value) || is_object($value)) {
            return "";
        }
        return mysqli_real_escape_string($this->connect(), (string)$value);
    }
}
