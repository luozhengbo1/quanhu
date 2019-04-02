<?php
//
//while(true){
//    $pid = pcntl_fork();
//    if($pid == -1){
//        die("创建子进程失败");
//    }else if($pid){
//        echo 2134,"father",PHP_EOL;
//        //父进程 会得到子进程号，所以这里是父进程执行的逻辑
//        pcntl_wait($status);//等待子进程中断，防止子进程成为僵尸进程。
//    }else{
//        echo 2134,"son",PHP_EOL;
//        //子进程得到的$pid=0,所以这里是子进程执行的逻辑
//    }
//}
//最大子进程数量
$maxchilderen = 8;

$currentchildpro=0;
function sig_handler($sig){
    global $currentchildpro ;
    switch ($sig){
        case SIGCHLD:
            echo 'SIGCHLD子进程退出',PHP_EOL;
            $currentchildpro--;
            break;
    }
}

declare(ticks=1);
//注册子进程处理信号函数
pcntl_signal(SIGCHLD,"sig_handler");
while (true){
    //创建一个子进程
    $pid = pcntl_fork();
    //父进程执行逻辑
    if($pid){
        // 数量大于了 最大数量就等待子进程退出
        if($currentchildpro >= $maxchilderen ){
            pcntl_wait($status);
        }
        //子进程执行的逻辑
    }elseif ($pid ==0){
        $s=rand(2,4);
        sleep($s);
        echo "child sleep $s second quit  ",PHP_EOL;
    }else{
        exit("子进程创建失败");

    }
}


//