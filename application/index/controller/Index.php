<?php
namespace app\index\controller;

use app\index\model\History;
use app\index\model\Hours;
use app\index\model\Days;
use app\index\model\Months;
use app\index\model\TodayCopy;
use RuiJie;
use app\index\model\Today;
use app\index\model\UserBasic;
use think\Controller;
use think\Db;

class Index extends Controller
{
    protected  static $userModel;
    protected  static $daysModel;
    protected  static $hoursModel;
    protected  static $monthsModel;
    protected  static $historyModel;
    protected  static $todayModel;

    public  function  index()
    {

        $this->assign('ip',$_SERVER['HTTP_HOST']);
        return $this->fetch('data/index');
    }
    public  function run()
    {
        set_time_limit(0);
        $address = "10.20.1.70";
        $port = 8084;
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_block($sock);
        socket_set_option($sock, SOL_SOCKET , SO_RCVBUF,33554432); # 接收缓冲区，为设置值的两倍，
        socket_bind($sock, $address, $port);
        $ruiJieAcceptor = new RuiJie();
        $time =time();
        do {
            $buf =socket_read($sock,20000);
            $probeInfo = $ruiJieAcceptor->analytic($buf);
            self::newUserBasic($probeInfo);
            self::toDay($probeInfo);//保存每天的数据
            self::store($probeInfo);//保存历史探针数据
//            chdir(APP_PATH."../public/");
//            popen("php index.php /index/index/delDays", "r");
//            dump($probeInfo);
//            echo "报文".$ruiJieAcceptor->getUdpNum()."条\r\n处理探针数量".$ruiJieAcceptor->getProbeNum()."条\r\n";
            //$this->delDays();
            if (time() - $time >= 60* 60) {
                $time =time();
                file_put_contents('error.log', "处理UDP报文".$ruiJieAcceptor->getUdpNum()."条\r\n处理探针数量".$ruiJieAcceptor->getProbeNum()."条\r\n",8);
            }
        } while (true);
    }
//    public  function run1()
//    {
//        set_time_limit(0);
//        $address = "10.20.1.70";
//        $port = 8084;
//        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
//        socket_set_block($sock);
//        socket_set_option($sock, SOL_SOCKET , SO_RCVBUF,33554432); # 接收缓冲区，为设置值的两倍，
//        socket_bind($sock, $address, $port);
//        $ruiJieAcceptor = new RuiJie();
//        $time =time();
//        do {
//            $buf =socket_read($sock,20000);
//            $probeInfo = $ruiJieAcceptor->analytic1($buf);
//            var_dump($probeInfo);die;
////            self::newUserBasic($probeInfo);
////            self::toDay($probeInfo);//保存每天的数据
//            self::toDayCopy($probeInfo);//保存每天的数据
////            self::store($probeInfo);//保存历史探针数据
////            chdir(APP_PATH."../public/");
////            popen("php index.php /index/index/delDays", "r");
//            dump($probeInfo);
////            echo "报文".$ruiJieAcceptor->getUdpNum()."条\r\n处理探针数量".$ruiJieAcceptor->getProbeNum()."条\r\n";
//            //$this->delDays();
//            if (time() - $time >= 60* 60) {
//                $time =time();
//               // file_put_contents('error.log', "处理UDP报文".$ruiJieAcceptor->getUdpNum()."条\r\n处理探针数量".$ruiJieAcceptor->getProbeNum()."条\r\n",8);
//            }
//        } while (true);
//    }

    public static function newUserBasic($data)
    {
        #逻辑整理  第一次用户来 上网的用户 24小时  7天  12 个 月 uv加1 PV+1  第二次来 PV+1 累计的所有加1
        # 先进行判断 在入库更新时间
        #生成近24小时 近7天  近12个月 的数据
        self::createBasicData();
        #入库前 进行判断
        self::$userModel = new UserBasic();
        self::$historyModel = new History();
        self::$todayModel = new Today();
        if(!empty($data)){
            $insert = [];
            $update = [];
            $time = time();
            $where=['mu_mac'=>$data['muMac']];
            $hours  = date('Y-m-d H:00:00');
            $days  = date('Y-m-d 00:00:00');
            $months  = date('Y-m-01');
            $total = 0;
            if($data['isAssociated']=="01"){  // 每次上网的人都加
                self::$hoursModel->where(['hours'=>$hours])->setInc('pv',1);
                self::$daysModel->where(['days'=>$days])->setInc('pv',1);
                self::$monthsModel->where(['months'=>$months])->setInc('pv',1);
                self::$historyModel->where(['id'=>1])->setInc('pv',1);
            }
            #所有触发的
            self::$historyModel->where(['id'=>1])->setInc('all',1);
            $userData = self::$userModel->where($where)->find();
            if( !$userData ){ //如果不存在做插入操作 第一次
                $insert['mu_mac'] = $data['muMac'];
                $insert['login_time'] = ($data['isAssociated']=="01")?$data['gettime']:null;
                $insert['status'] = $data['isAssociated'];
                if($data['isAssociated']=="01"){  //  上网的用户 进行统计 24小时
                    //没有这个用户 第一次 给近24小时 中的这个小时加1 这天加1 这月加1
                    self::$hoursModel->where(['hours'=>$hours])->setInc('uv',1);
                    self::$daysModel->where(['days'=>$days])->setInc('uv',1);
                    self::$monthsModel->where(['months'=>$months])->setInc('uv',1);
                }
                self::$userModel->save($insert);
            }else{ //存在做更新操作
                if($data['isAssociated']=="01"){  //  上网的用户 进行统计
                    $upHours  = date('Y-m-d H:00:00',time()+3600); //下一个小时
                    $checkHours = self::$userModel->where(['mu_mac'=>$data['muMac'],'login_time'=>['between',[$hours,$upHours] ]])->find();
                    if(!$checkHours){ //检查在这个小时之内没有加1
                        self::$hoursModel->where(['hours'=>$hours])->setInc('uv',1);
                    }
                    $upDays = date('Y-m-d 00:00:00',time()+24*60*60);
                    $checkDays = self::$userModel->where(['mu_mac'=>$data['muMac'],'login_time'=>['between',[$days,$upDays] ]])->find();
                    if(!$checkDays){ // 检查在今天之内没有最新数据 进行加1
                        self::$daysModel->where(['days'=>$days])->setInc('uv',1);
                    }
                    $upMonths =date("Y-m-01",strtotime("$days +1 month -1 day"));
                    $checkMonths = self::$userModel->where(['mu_mac'=>$data['muMac'],'login_time'=>['between',[$months,$upMonths] ]])->find();
                    if(!$checkMonths){
                        self::$monthsModel->where(['months'=>$months])->setInc('uv',1);
                    }
                }

                $longTime = 0;
                if($data['isAssociated']=="02" && $userData['login_time'] ){ // 存在上次登录的时间 检测到这次下线 更新下线时间
                    $update['logout_time'] =$data['gettime'];
                    $longTime = $time - strtotime($userData['login_time']);
                    $update['long_time'] = $longTime+$userData['long_time'];
                    #將状态更新到
                     self::$todayModel->save(['isAssociated'=>"02"],['muMac'=>$data['muMac']]);
                }else if($data['isAssociated'] =="01" &&  !$userData['login_time'] ){ // 上次没登录 这次登录更新 上线时间
                    $update['login_time'] =$data['gettime'];
                }else if($data['isAssociated'] =="01" && $userData['login_time'] ){ //上次登录了 这次也登陆  更新两个时间 不做时间结算。
                    $longTime = $time - strtotime($userData['login_time']);
                    $update['login_time'] =$data['gettime'];
                    $update['logout_time'] = null;
                    $update['long_time'] = $longTime+$userData['long_time'];
                }
                $update['status'] = $data['isAssociated'];
                self::$userModel->save($update,$where);
            }
        }


    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 建基础表
     */
    protected static function createBasicData()
    {
        $hours = date('Y-m-d H:00:00');
        self::$hoursModel = new Hours();
        if(!self::$hoursModel ->where(['hours'=>$hours])->find()){
            self::$hoursModel->save(['hours'=>$hours]);
        }
        $days = date('Y-m-d 00:00:00');
        self::$daysModel = new Days();
        if(! self::$daysModel  ->where(['days'=>$days])->find()){
            self::$daysModel ->save(['days'=>$days]);
        }
        $months = date('Y-m-01 00:00:00');
        self::$monthsModel = new Months();
        if(! self::$monthsModel  ->where(['months'=>$months])->find()){
            self::$monthsModel ->save(['months'=>$months]);
        }
        #删除 超过1 天的数据
    }


    /**
 * 每日数据表
 */
    protected static function toDay($data)
    {
        self::$todayModel  = new Today();
        if(!empty($data)){

            if(self::$todayModel  ->save($data)){
                return 'today数据插入成功';
            }else{
                return 'today数据插入失败';
            }
        }

    }
    /**
     * 每日数据表
     */
    protected static function toDayCopy($data)
    {
        self::$todayModel  = new TodayCopy();
        if(!empty($data)){

            if(self::$todayModel  ->save($data)){
                return 'today数据插入成功';
            }else{
                return 'today数据插入失败';
            }
        }

    }

    /**
     * 删除超过24小时的数据的数据
     */
    public function delDays()
    {
        $time = time();
        $res = Db('today')->where(['gettime'=>['<',date('Y-m-d H:i:s',time()-24*60*60)]])->delete();
    }
    /**
     *将探针数据保存成日志文件
     */
    protected static function store($data)
    {
        file_put_contents('/opt/qhdata/'.date('Ym').'rjtzdata.txt',json_encode($data).",",FILE_APPEND);
    }
}
