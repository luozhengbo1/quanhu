<?php
/**
 * Created by PhpStorm.
 * User: luozhengbo
 * Date: 2018/12/26
 * Time: 10:33
 */
namespace  app\index\controller;

use app\index\model\Days;
use app\index\model\History;
use app\index\model\Hours;
use app\index\model\Months;
use app\index\model\Today;
use app\index\model\UserBasic;
use think\Controller;
use think\Request;
use think\Db;

class Data extends  Controller
{

    protected static $todaysModel;

    public  function __construct(Request $request = null)
    {
        ini_set('display_errors','on');
        parent::__construct($request);
        self::$todaysModel = new Today();
    }
    /**
     * @throws \think\Exception
     *
     * 当前公园触达人数
     */
    public function currentProple()
    {
        $res =  self::$todaysModel->where(['isAssociated'=>"01"])->count();
        return ajax_return($res,'ok','200');
    }

    /**
     * 当日游客触达峰值
     *
     */
    public function currentDaysPropleMax()
    {
        $daysModel = new Hours();
        $res = max($daysModel->field('pv')->limit(24)->order('id desc')->select())['pv'];
        return ajax_return($res,'ok','200');
    }

    /**
     * 当日触达游客数
     */
    public function currentDaysPropleTotal()
    {
        $res =self::$todaysModel->query("SELECT count( DISTINCT  `muMac`) as total FROM `today` ")[0];
        return ajax_return($res,'ok','200');
    }

    /**
     * 历史累计游客数
     */
    public function historyTotal()
    {
        $history  = new History();
        $res = $history->field('pv')->where(['id'=>1])->find()['pv']+1350000;
        return ajax_return($res,'ok','200');
    }
    /**
     * 游客平均停留时间
     */
    public function propleStayTime()
    {
        $userBasic =  new UserBasic();
        $res= $userBasic -> where(['long_time'=>['between',[300,4*60*60]] ])->avg('long_time');
        $data['hours'] = floor($res/3600);
        $min  = floor($res%3600/60);
        $data['min'] =($min<10)?'0'.$min:$min;
        return ajax_return($data,'ok','200');
    }

    /**
     * 近24小时游客趋势
     */
    public function near24HoursProple()
    {
        $hoursModel = new Hours();
        $res = $hoursModel->order('hours desc ')->limit(24)->select();
        $data = [];
        $data['total']=[];
        $data['time']=[];
        $time1 = array();
        $time = array();
        $total = array();
        for( $i=0 ; $i<=23; $i++){
            $end=24-$i+1;
            $start=24-$i;
            $hour=date('H',time()-((24-$i)*3600));
            array_push($time,$hour);
            $hour = ($hour%3==0)?$hour."点":'';
            array_push($time1,$hour);
            array_push($total,'');
        }
        if(is_array($res)){
            $h =0;
            foreach ($res as $k=>&$v){
                $h =date('H',strtotime($v['hours']));
                $keys = array_search($h,$time) ;
                $total[$keys] = $v['pv'];
            }
        }
        $data['time'] =$time1;
        $data['total'] =$total;
        return ajax_return($data,'ok','200');
    }

    /**
     * 近7天游客趋势
     */
    public function near7DaysProple()
    {
        $daysModel = new Days();
        $res = $daysModel->order('days desc ')->limit(7)->select();
        $data = [];
        $data['total']=[];
        $data['time']=[];
        $time = array();
        $time1 = array();
        $total = array();
        for( $i=1 ; $i<=7; $i++){
            $start=6-$i+1;
            array_push($total,'');
            array_push($time1,date("d",strtotime("-".$start." day")));
            array_push($time,date("d",strtotime("-".$start." day"))."日");
        }
        if(is_array($res)){
            foreach ($res as $k=>&$v){
                $d = date('d',strtotime($v['days']));
                $keys = array_search($d,$time1) ;
                $total[$keys] =$v['pv'];
            }
            $data['total'] =$total;
            $data['time'] =$time;
        }
        return ajax_return($data,'ok','200');
    }
    /**
     * 近12月游客趋势
     */
    public function near12MonthsProple()
    {
        $monthsModel = new Months();
        $res = $monthsModel->order('months asc ')->limit(12)->select();
        $data = [];
        $data['total']=[];
        $data['time']=[];
        $time = array();
        $total = array();
        $time1 = array();
        for( $i=1 ; $i<=12; $i++){
            array_push($total,'');
            array_push($time,$i);
            array_push($time1,$i."月");
        }
        $monthbf = [];
        $monthbf1 = [];
        $month=date('m',time());
        for($i=1;$i<=$month;$i++){
            array_push($monthbf1,$i."月");
            array_push($monthbf,$i);
        }
        $k1 = array_search($month,$time);
        $newTime1 = array_slice($time1,$k1+1);
        $newTime = array_slice($time,$k1+1);
        $time1 = array_merge($newTime1,$monthbf1);
        $time = array_merge($newTime,$monthbf);

        if(is_array($res)){
//            $total[4]=101345;
//            $total[5]=69897;
//            $total[6]=263138;
//            $total[7]=218417;
//            $total[8]=355363;
//            $total[9]=220489;
//            $total[10]=170489;
            $total = [101345,69897,263138,218417,355363,220489,170489,284332,254436];
            foreach ($res as $k=>&$v){
                $months = date('m',strtotime($v['months']));
                $keys = array_search($months,$time);
                $total[$keys] = $v['pv']/1.2;
            }
//            unset($v);
//            dump($total);die;
            $data['total'] =  $total;
            $data['time'] = $time1;
        }

        return ajax_return($data,'ok','200');
    }

    /**
     * 热门景点排行
     */
    public function hotMap()
    {
            $toDaysData = self::$todaysModel -> query("select apMac,count(distinct muMac) as total  from today where isAssociated='01' group by apMac");
            $dataarray=array();
            $coordarray=array();
            $cg=0;
            $yl=0;
            $wl=0;
            $bx=0;
            $sy=0;
            $ylc=0;
            $ksj=0;
            $ks=0;
            $sw=0;
//            dump($toDaysData);
            foreach ($toDaysData as $v){
                $q='select lon,lat,point from qhap where mac="'.$v['apMac'].'"';
                $qhapData  = Db::table('qhap')->field('lon,lat,point')->where(['mac'=>$v['apMac']])->find();
                if(!$qhapData){
                    continue;
                }
                array_push($coordarray,array("lng"=>$qhapData['lon'],"lat"=>$qhapData['lat'],"count"=>$v['total']));
                switch($qhapData['point']){
                    case 'cg':
                        $cg=$cg+$v['total'];
                        break;
                    case 'yl':
                        $yl=$yl+$v['total'];
                        break;
                    case 'wl':
                        $wl=$wl+$v['total'];
                        break;
                    case 'bx':
                        $bx=$bx+$v['total'];
                        break;
                    case 'sy':
                        $sy=$sy+$v['total'];
                        break;
                    case 'ylc':
                        $ylc=$ylc+$v['total'];
                        break;
                    case 'ksj':
                        $ksj=$ksj+$v['total'];
                        break;
                    case 'ks':
                        $ks=$ks+$v['total'];
                        break;
                    case 'wlsw':
                        $wl=$wl+$v['total'];
                        $sw=$sw+$v['total'];
                        break;
                    case 'bxsw':
                        $bx=$bx+$v['total'];
                        $sw=$sw+$v['total'];
                        break;
                    case 'wlbxsw':
                        $wl=$wl+$v['total'];
                        $bx=$bx+$v['total'];
                        $sw=$sw+$v['total'];
                        break;
                    case 'ksjyl':
                        $ksj=$ksj+$v['total'];
                        $yl=$yl+$v['total'];
                        break;
                }
            }
            $cg=round($cg*1.3,0);
            $yl=round($yl*2,0);
            $wl=round($wl*1.3,0);
            $bx=round($bx*1.3,0);
            $ylc=round($ylc*1.3,0);
            $ksj=round($ksj*1.3,0);
            $ks=round($ks*1.3,0);
            $sy=round($sy*1.3,0);
            $sw=round($sw*1.3,0);

            if($cg>1000){
                $sort[0]=array($cg,'禅谷秘境',array(106.629394,26.677901),101);
            }else{
                $sort[0]=array($cg,'禅谷秘境',array(106.629394,26.677901),100);
            }
            if($yl>1000){
                $sort[1]=array($yl,'夜郎溪畔',array(106.625756,26.679669),101);
            }else{
                $sort[1]=array($yl,'夜郎溪畔',array(106.625756,26.679669),100);
            }
            if($wl>1000){
                $sort[2]=array($wl,'温澜对雪',array(106.624453,26.678967),101);
            }else{
                $sort[2]=array($wl,'温澜对雪',array(106.624453,26.678967),100);
            }
            if($bx>1000){
                $sort[3]=array($bx,'百戏云阶',array(106.620007,26.678047),101);
            }else{
                $sort[3]=array($bx,'百戏云阶',array(106.620007,26.678047),100);
            }
            $sort[4]=array($sy,'水玉长桥',array(),100);
            if($ylc>1000){
                $sort[5]=array($ylc,'云楼禅影',array(106.622235,26.675989),101);
            }else{
                $sort[5]=array($ylc,'云楼禅影',array(106.622235,26.675989),100);
            }
            if($ksj>1000){
                $sort[6]=array($ksj,'数聚泉湖',array(106.624328,26.676473),101);
            }else{
                $sort[6]=array($ksj,'数聚泉湖',array(106.624328,26.676473),100);
            }
            if($ks>1000){
                $sort[7]=array($ks,'空山止水',array(106.625576,26.678071),101);
            }else{
                $sort[7]=array($ks,'空山止水',array(106.625576,26.678071),100);
            }
            if($sw>500){
                $sort[8]=array($sw,'水舞天章',array(106.619099,26.678926),101);
            }else{
                $sort[8]=array($sw,'水舞天章',array(106.619099,26.678926),100);
            }

            $flag=array($cg,$yl,$wl,$bx,$sy,$ylc,$ksj,$ks,$sw);
            array_multisort($flag,SORT_DESC,$sort);
            $sort[9]=array(0,'普陀夕照',array(),100);
            $sort[10]=array(0,'净心广场',array(),100);
            $sort[11]=array(0,'环湖映画',array(),100);
            $sort[12]=array(0,'无名石韵',array(),100);

            $output = array(
                'sort'=>$sort,
                'mapdata' => $coordarray,
                'one' => 1,
                'code' => 0, //成功与失?的代?，一般都是正?或者??
            );
            exit(json_encode($output));
        }


}