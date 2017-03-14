<?php

/**
 * Error log parser
 * @version 1.0
 *
 */
class errorLogParser
{
    private $logPath;
    private $dbName;
    private $dbHost;
    private $dbUser;
    private $dbPass;

    public function __construct($logPath, $dbName, $dbHost, $dbUser, $dbPass)
    {
        $this->logPath = $logPath;
        $this->dbName  = $dbName;
        $this->dbHost  = $dbHost;
        $this->dbUser  = $dbUser;
        $this->dbPass  = $dbPass;
    }

    /**
     *
     * @return boolean
     */
    public function getLog()
    {
        if (!empty($this->logPath)) {
            return file($this->logPath);
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function parseLog()
    {
        if ($log = $this->getLog()) {
            $re     = '/(?=to=<(.*)>,)|(?=stat=(.*))/';
            $errors = [];
            foreach ($log as $key => $logLine) {
                preg_match_all($re, $logLine, $matches, PREG_SPLIT_NO_EMPTY);
                $errors[$key]['email'] = $matches['1']['0'];
                $errors[$key]['error'] = $matches['2']['1'];
            }
            return $errors;
        }
        return false;
    }

    /**
     *
     * @param boolean $onlyEmails
     * @param boolean $onlyErrors
     */
    public function getLogInfo($onlyEmails = false, $onlyErrors = false)
    {
        if (!is_null($log = $this->parseLog())) {
            foreach ($log as $line) {
                echo!$onlyErrors ? "Error: ".$line['error']."<br> " : "";
                echo!$onlyEmails ? "To email: ".$line['email']."<br><br>" : "";
            }
        }
    }

    /**
     *
     * @return object
     */
    public function dbConnect()
    {

        $mysqli = new mysqli($this->dbHost, $this->dbUser, $this->dbPass,
            $this->dbName);
        if ($mysqli->connect_errno) {
            printf("Connection failed: %s\n", $mysqli->connect_error);
            exit();
        }
        return $mysqli;
    }

    /**
     *
     * @param string $table
     * @param string $emailColumn
     * @param string $flagColumn
     * @return boolean
     */
    public function setEmailsFlag($table, $emailColumn, $flagColumn)
    {
        if (!is_null($log = $this->parseLog()) && $con = $this->dbConnect()) {

            foreach ($log as $line) {
                if (!empty($line['error']) && !empty($line['email'])) {
                    if (mysqli_num_rows($con->query("SELECT * FROM $table WHERE $emailColumn = '".$line['email']."'"))
                        > 0) {
                        $con->query("UPDATE $table SET $flagColumn = '".$line['error']."' WHERE $emailColumn = '".$line['email']."'");
                    }
                }
            }
        }

        return false;
    }
}