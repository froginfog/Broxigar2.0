<?php
namespace  lib\core;

use Workerman\Worker;
use PHPSocketIO\SocketIO;

class Socket {
     private static $uidMap = [];

     public function start(){
         if(!preg_match("/cli/i", php_sapi_name())) die('只能在命令函模式下执行');

         $io = new SocketIO(2120);

         $io->on('workerStart', function () use ($io) {
             $worker = new Worker('http://127.0.0.1:3120');
             $worker->onMessage = function ($http_connection, $data) use ($io) {
//                 print_r($data);
                 if(isset($data['post']['to'])) {
                     $io->to($data['post']['to'])->emit('serverToClient', $data['post']);
                 } else {
                     $io->emit('serverToClient', $data['post']);
                 }
                 $http_connection->send('ok');
             };
             $worker->listen();
         });

         $io->on('connection', function ($socket){
             $socket->on('login', function ($uid) use ($socket) {
                if(isset(self::$uidMap[$uid])) return;
                self::$uidMap[$uid] = $uid;
                $socket->join($uid);
                $socket->uid = $uid;
                print_r(self::$uidMap);
             });

             $socket->on('disconnect', function()use($socket){
                 if(!isset($socket->uid)) return;
                 unset(self::$uidMap[$socket->uid]);
                 print_r(self::$uidMap);
             });

//             $socket->on('clientToClient', function($data)use($socket){
//
//             });
         });

         Worker::runAll();
     }

    /**
     * post 方式发送数据
     * @param $data array
     * $data数组结构 ['to' => '要推送给哪个id的客户端', 'content'=>'要推送的内容']，没有'to'时为广播
     */
     public function serverToClient($data){
         $url = "http://127.0.0.1:3120";
         $ch = curl_init ();
         curl_setopt ( $ch, CURLOPT_URL, $url );
         curl_setopt ( $ch, CURLOPT_POST, 1 );
         curl_setopt ( $ch, CURLOPT_HEADER, 0 );
         curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
         curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
         curl_exec ( $ch );
         curl_close ( $ch );
     }
}
