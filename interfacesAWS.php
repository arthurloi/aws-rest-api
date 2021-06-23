<?php

interface InjectLoggerInterface
{
    public function writeLogs($logs_content,$logfile);
}

interface InjectOutputInterface
{
    public function echoTest($msg);
}

class Writer implements InjectLoggerInterface,InjectOutputInterface{

    private $logfile;
    private $silent;

    public function __construct(string $logfile, BOOL $silent = FALSE){
        $this->logfile = $logfile;
        $this->silent = $silent;
    }

    public function echoTest($msg){
        if($this->silent == FALSE){
            echo($msg);
        }
    }

    public function writeLogs($logs_content,$logfile){
        $logs_header = '['.date("Y-m-d H:i:s").']';
        $logs = $logs_header."\n".$logs_content."\n";

        error_log($logs."\n",3,$logfile);
    }

    /**
     * @return string
     */
    public function getLogfile(): string
    {
        return $this->logfile;
    }

    /**
     * @return bool
     */
    public function isSilent(): bool
    {
        return $this->silent;
    }

}