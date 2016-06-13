<?php
    include("populi.class.php");
    $num_args=sizeof($argv)-1;
    $task=$argv[1];
    $populi=new Populi();

    $arguments=array();

    //echo $num_args."\n";
    for($i=2; $i<=$num_args; $i++){
        if($i%2 == 0){
            $arguments[$argv[$i]]=$argv[$i+1];
        }
    }
//    print_r($arguments);

    $xml=simplexml_load_string($populi->doTask($task,$arguments,true));
    /*
    if($num_args > 1){
        $i=2;
        while($i<sizeof($argv)){
            $argue[]=array($argv[$i]=>$argv[$i+1]);
        }
        $xml=simplexml_load_string($populi->doTask($task,$argue,true));
    }
    else{
        $xml=simplexml_load_string($populi->doTask($task,NULL,true));
    }
    */
    echo "\n";
    print_r($xml);
?>