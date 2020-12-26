<?php
namespace App\Helpers;
/**
 * Created by PhpStorm.
 * User: faizal
 * Date: 5/8/18
 * Time: 5:31 PM
 */
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
//use App\Libraries\Logger\\Exception;


class RabbitMq
{
    protected $connection;
    protected $HOST,$PORT,$USER,$PASS,$VHOST;
    public function __construct($HOST,$PORT,$USER,$PASS,$VHOST)
    {
        $this->HOST = $HOST;
        $this->PORT = $PORT;
        $this->PASS = $PASS;
        $this->VHOST = $VHOST;
        $this->connection   = new AMQPStreamConnection($HOST, $PORT, $USER, $PASS, $VHOST);
    }

    public function info($text){
        echo '*** ['.date("Y-m-d H:i:s").'] Message : '.$text."\n";
    }

    public function CloseConnection(){
        $this->connection->close();
    }

    //param 1 : nama queue/kanal
    //param 2 : nama class yang dipanggil
    //param 3 : nama fungsi yang dipanggil
    //param 4 : pemanggilan diulang berapa kali (1-9)
    //param 5 : data yang dikirim
    //param 6 : settingan close koneksi rabbitmq ketika mengeksekusi sendingjob
    public function SendingJob($queue,$class,$function,$loop,$data,$disable_close_connection=0){
        try{
            $channel = $this->connection->channel();
            $channel->queue_declare(
                $queue,
                $passive = false,
                $durable = true,
                $exclusive = false,
                $auto_delete = false,
                $nowait = false,
                $arguments = null,
                $ticket = null
            );

            $param  = [
                'class'     =>$class,
                'function'  =>$function,
                'loop'      =>$loop,
                'data'      =>$data
            ];
            $param  = json_encode($param);
            $msg = new AMQPMessage($param,[
                'delivery_mode'=>AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);
            $channel->basic_publish($msg, '', $queue);
            $channel->close();

            if(empty($disable_close_connection)){
                $this->connection->close();
            }
            return [
                'status'=>1,
                'message'=>"success"
            ];
        }catch (\Exception $e){
            return [
                'status'=>0,
                'message'=>$e->getMessage()
            ];
        }
    }

    public function MultiSendingJob($queue,$data,$disable_close_connection=0){
        try{
            $channel = $this->connection->channel();
            $channel->queue_declare(
                $queue,
                $passive = false,
                $durable = true,
                $exclusive = false,
                $auto_delete = false,
                $nowait = false,
                $arguments = null,
                $ticket = null
            );
            foreach($data as $key => $value){
                $param  = [
                    'class'     =>$value['class'],
                    'function'  =>$value['function'],
                    'loop'      =>$value['loop'],
                    'data'      =>$value['data']
                ];
                $param  = json_encode($param);
                $msg = new AMQPMessage($param,[
                    'delivery_mode'=>AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]);
                $channel->basic_publish($msg, '', $queue);
            }
            $channel->close();
            if(empty($disable_close_connection)){
                $this->connection->close();
            }
        }catch (\Exception $e){
            return [
                'status'=>0,
                'message'=>$e->getMessage()
            ];
        }
    }

    public function ListenJob($queue){
        try {
            $channel = $this->connection->channel();
            $channel->queue_declare(
                $queue,
                $passive = false,
                $durable = true,
                $exclusive = false,
                $auto_delete = false,
                $nowait = false,
                $arguments = null,
                $ticket = null
            );
            $this->info('LISTENING ON QUEUE : ' . $queue . "\n");
            $callback = function (AMQPMessage $msg) use ($queue, $channel) {
                $data = json_decode($msg->body, 1);
                $is_valid = 1;
                if (!array_key_exists('class', $data)) {
                    $is_valid = 0;
                    $this->info('Class Not Found');
                }
                if (!array_key_exists('function', $data)) {
                    $is_valid = 0;
                    $this->info('Function Not Found');
                }
                if (!array_key_exists('loop', $data)) $data['loop'] = 0;
                if (!array_key_exists('data', $data)) $data['data'] = [];
                $strClass = $data['class'];
                $strFunc = $data['function'];
                $loop = $data['loop'];
                $param = $data['data'];

                $properties = $msg->get_properties();
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                if ($loop > 9) {
                    $state = 'failed';
                    $is_valid = 0;
                    $failedQueue = $queue . '.' . $state;
                    $channel->queue_declare(
                        $failedQueue,
                        $passive = false,
                        $durable = true,

                        $exclusive = false,
                        $auto_delete = false,
                        $nowait = false,
                        $arguments = null,
                        $ticket = null
                    );
                    $param = $data;
                    $param['loop'] = 0;
                    $param = json_encode($param);

                    $msg = new AMQPMessage($param, $properties);
                    $channel->basic_publish($msg, '', $failedQueue);
                    $this->info("Moving to failed queue {$failedQueue}");
                    $this->info('Reach Limit Execution');
                }
                if(!class_exists($strClass)){
                    $is_valid = 0;
                    $this->info('Class: '.$strClass." Not Found");
                    $param = [
                        'class' => $strClass,
                        'function' => $strFunc,
                        'loop' => $loop + 1,
                        'data' => $data['data']
                    ];
                    $param = json_encode($param);
                    $msg = new AMQPMessage($param, $properties);
                    $channel->basic_publish($msg, '', $queue);
                }

                if ($is_valid == 1) {
                    try {
                        $this->info("CLASS : " . $data['class']);
                        $this->info("FUNCTION : " . $data['function']);
                        $this->info("LOOP : " . $data['loop']);
                        $this->info("DATA : " . json_encode($data['data']));

                        $Class = new $strClass();
                        $result = $Class->$strFunc($param);

                        if ($result['status'] != 1) {
                            $this->info('Process Failed :'.$result['message']);
                            throw new \Exception($result['message']);
                        }
                        $this->info('Process Success');
                    } catch (\Exception $e) {
                        $this->info("Error Raise : " . $e->getMessage());
                        $this->info('Trying to Re-Register Job');

                        $param = [
                            'class' => $strClass,
                            'function' => $strFunc,
                            'loop' => $loop + 1,
                            'data' => $data['data']
                        ];
                        $param = json_encode($param);
                        $msg = new AMQPMessage($param, $properties);
                        $channel->basic_publish($msg, '', $queue);
                    }
                }
            };
            $channel->basic_qos(null, 1, null);
            $channel->basic_consume(
                $queue,
                $consumer_tag = '',
                $no_local = false,
                $no_ack = false,
                $exclusive = false,
                $nowait = false,
                $callback
            );
            while (count($channel->callbacks)) {
                $channel->wait();
            }
            $channel->close();
            $this->connection->close();
            return [
                'status'=>1,
                'message'=>'success'
            ];
        }catch (\Exception $e){
            $this->info("Error Raise : " . $e->getMessage());
            return [
                'status'=>1,
                'message'=>$e->getMessage()
            ];
        }
    }
}