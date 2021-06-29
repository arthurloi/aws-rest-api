<?php

require 'vendor/autoload.php';
//require 'interfacesAWS.php';
use Aws\Ec2\Ec2Client;

/**
 * Class InstanceManager
 */
class InstanceManager {
    private Ec2Client $ec2client;
    private Writer $writer;

    public function __construct($region,$key,$secret,Writer $writer)
    {
        $this->ec2client = new Ec2Client([
            'region'=>$region,
            'version'=>'2016-11-15',
            'credentials' =>[
                'key'=>$key,
                'secret'=>$secret,
            ],
        ]);
        $this->writer= $writer;

    }

        /**
     * Démarre une instance EC2 à l'aide de son ID.
     *
     * @param string $instanceId
     * @param bool $async
     * @return string
     */
    public function start(string $instanceId, bool $async = TRUE): string
    {
        set_exception_handler(
            function ($e) {

                $this->writer->echoTest(json_encode(array(
                    'error' => array(
                        'code' => $e->getAwsErrorCode(),
                        'message' => $e->getMessage()
                    )
                ),JSON_PRETTY_PRINT));

                $this->writer->writeLogs(json_encode(array(
                    'error' => array(
                        'code' => $e->getAwsErrorCode(),
                        'message' => $e->getMessage()
                    )
                ),JSON_PRETTY_PRINT),$this->writer->getLogfile());


            });

        try{
            if($async == TRUE){

                $this->ec2client->startInstances(array(
                    'InstanceIds' => array($instanceId),
                ));
                $this->writer->writeLogs($this->instanceInfo($instanceId),$this->writer->getLogfile());
                return $this->instanceInfo($instanceId);

            }else{
                $this->ec2client->startInstances(array(
                    'InstanceIds' => array($instanceId),
                ));
                $this->ec2client->waitUntil('InstanceRunning', [
                    'InstanceIds' => array($instanceId),
                ]);
                $this->writer->writeLogs($this->instanceInfo($instanceId),$this->writer->getLogfile());
                return $this->instanceInfo($instanceId);
            }
        }catch (Ec2Exception $e){
            //throw new Exception('Une exception a été lancée. Message d\'erreur : '. $e->getMessage()) ;
        }


    }

    /**
     * Stoppe une instance EC2 à l'aide de son ID.
     *
     * @param string $instanceId
     * @param bool $async
     * @return string
     */
    public function stop(string $instanceId, bool $async = TRUE): string
    {
        set_exception_handler(
            function ($e) {

                $this->writer->echoTest(json_encode(array(
                    'error' => array(
                        'code' => $e->getAwsErrorCode(),
                        'message' => $e->getMessage()
                    )
                ),JSON_PRETTY_PRINT));

                $this->writer->writeLogs(json_encode(array(
                    'error' => array(
                        'code' => $e->getAwsErrorCode(),
                        'message' => $e->getMessage()
                    )
                ),JSON_PRETTY_PRINT),$this->writer->getLogfile());


            });

        try{
            if($async == TRUE){

                $this->ec2client->stopInstances(array(
                    'InstanceIds' => array($instanceId),
                ));
                $this->writer->writeLogs($this->instanceInfo($instanceId),$this->writer->getLogfile());
                return $this->instanceInfo($instanceId);

            }else{

                $this->ec2client->stopInstances(array(
                    'InstanceIds' => array($instanceId),
                ));
                $this->ec2client->waitUntil('InstanceStopped', [
                    'InstanceIds' => array($instanceId),
                ]);
                $this->writer->writeLogs($this->instanceInfo($instanceId),$this->writer->getLogfile());
                return $this->instanceInfo($instanceId);
            }
        }
        catch (Ec2Exception $e){

        }

    }


    /**
     * Redémarre une instance EC2 à l'aide de son ID.
     *
     * @param $instanceid
     */
    public function restart($instanceid){

        set_exception_handler(
            function ($e) {

                $this->writer->echoTest(json_encode(array(
                    'error' => array(
                        'code' => $e->getAwsErrorCode(),
                        'message' => $e->getMessage()
                    )
                ),JSON_PRETTY_PRINT));

                $this->writer->writeLogs(json_encode(array(
                    'error' => array(
                        'code' => $e->getAwsErrorCode(),
                        'message' => $e->getMessage()
                    )
                ),JSON_PRETTY_PRINT),$this->writer->getLogfile());


            });

        $this->ec2client->stopInstances(array(
            'InstanceIds' => array($instanceid),
        ));

        $this->ec2client->waitUntil('InstanceStopped', [
            'InstanceIds' => array($instanceid),
        ]);

        $this->ec2client->startInstances(array(
            'InstanceIds' => array($instanceid),
        ));

        $this->writer->writeLogs($this->instanceInfo($instanceid),$this->writer->getLogfile());
        return $this->instanceInfo($instanceid);
    }

    /**
     * Retourne les informations à propos du client.
     *
     */
    public function clientInfo(){
        return $this->ec2client->describeInstances();
    }

    /**
     * Retourne les informations d'une instance EC2 à l'aide de son ID.
     *
     * @param $instanceid
     * @return string
     */
    public function instanceInfo($instanceid): string{
        $result = $this->ec2client->describeInstances([
            'InstanceIds' => [
                $instanceid,
            ],
        ]);

        return $result;
    }

    /**
     * @param $instanceid
     * @param $waiter
     */
    public function wait($instanceid, $waiter): void
    {
        $this->ec2client->waitUntil($waiter, [
            'InstanceIds' => array($instanceid),
        ]);

    }

    /**
     * Retourne la liste des instances en fonction de l'état demandé.
     *
     *
     * @param string $state
     * $state peut être 'running' ou 'stopped'
     *
     * @return string
     */
    private function getInstancesByState(string $state): string
    {
        $result = $this->clientInfo();

        $listInstances = '';

        foreach($result['Reservations'] as $instances)
        {
            if($instances['Instances'][0]['State']['Name'] == $state){
                $listInstances = $listInstances .$instances['Instances'][0]['InstanceId'].' ';
            }
        }

        return $listInstances;
    }

    /**
     * Retourne la liste des instances en cours d'exécution.
     *
     * @return string
     */
    public function getRunningInstances(): string
    {
        return $this->getInstancesByState('running');
    }

    /**
     * Retourne la liste des instances stoppées.
     *
     * @return string
     */
    public function getStoppedInstances(): string
    {
        return $this->getInstancesByState('stopped');
    }

    /**
     * Retourne la liste des instances du client dans un tableau.
     *
     * @return array
     */
    public function getIdInstances(){
        set_exception_handler(
            function ($e) {

                $this->writer->echoTest(json_encode(array(
                    'error' => array(
                        'code' => $e->getAwsErrorCode(),
                        'message' => $e->getMessage()
                    )
                ),JSON_PRETTY_PRINT));

                $this->writer->writeLogs(json_encode(array(
                    'error' => array(
                        'code' => $e->getAwsErrorCode(),
                        'message' => $e->getMessage()
                    )
                ),JSON_PRETTY_PRINT),$this->writer->getLogfile());


            });

        $result = $this->clientInfo();

        $listInstances=array();

        foreach($result['Reservations'] as $instances){
            $listInstances[] = $instances['Instances'][0]['InstanceId'];
        }

        return $listInstances;
    }

    /**
     * Retourne l'état d'une instance à l'aide de son ID.
     *
     * @param $instanceid
     * @return mixed|string
     */
    public function instanceStatus($instanceid){
        $clientinfo = $this->clientInfo();

        $instancestate='';

        foreach($clientinfo['Reservations'] as $instances){
            if($instances['Instances'][0]['InstanceId'] == $instanceid){
                $instancestate = $instances['Instances'][0]['State']['Name'];
            }
        }

        return $instancestate;
    }
}

