<?php
    include("populi.class.php");
    $num_args=sizeof($argv)-1;
    $task=$argv[1];
    $populi=new Populi(); // if no token is given, see populi.class.php for $populi->login

    $arguments=array();
    for($i=2; $i<=$num_args; $i++){
        if($i%2 == 0){
            $arguments[$argv[$i]]=$argv[$i+1];
        }
    }

    $xml=simplexml_load_string($populi->doTask($task,$arguments,true));
    echo "\n";
    print_r($xml);
    $populi->logout();
?>