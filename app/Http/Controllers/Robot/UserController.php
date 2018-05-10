<?php
/**
 * Created by PhpStorm.
 * User: xu
 * Date: 2018/5/10
 * Time: 14:15
 */

namespace App\Http\Controllers\Robot;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * @param $r_uid 用戶ID
     * @param $f_uid 好友ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRelationship($r_uid,$f_uid){
        $data = [];
        $input['r_uid'] = $r_uid;
        $input['f_uid'] = $f_uid;
        $count = DB::table('robot_friend_mapping')->where($input)->count();
        if ($count){
            return response()->json(['status'=>0,'err_msg'=>"已存在好友關係"]);
        }
        $input['created_at'] = Carbon::now();
        $data[] = $input;
        $input['r_uid'] = $f_uid;
        $input['f_uid'] = $r_uid;
        $input['created_at'] = Carbon::now();
        $data[] = $input;
        try{
            $res = DB::table('robot_friend_mapping')->insert($data);
            if ($res)
                return response()->json(['status'=>1,'err_msg'=>"success"]);
        }catch(\PDOException $e){
            return response()->json(['status'=>0,'err_msg'=>"數據庫插入失敗",'detail'=>$e->errorInfo]);
        }
    }

    public function getHxId($robot_id){
        $result = \DB::table('RMapEase')->where('RUId',$robot_id)->first();
        if ($result){
            return response()->json(['status'=>1,'hx_id'=>$result->EAccount]);
        }else{
            return response()->json(['status'=>0,'msg'=>'環信ID不存在']);
        }
    }

    public function getFriendship($robot_id){
        $result = [];
        $f_uids = DB::table('robot_friend_mapping')
                    ->select('f_uid')
                    ->where('r_uid',$robot_id)
                    ->get()->pluck('f_uid');
        foreach ($f_uids as $key=>$f_uid){
            $rmap = \DB::table('RMapEase')
                ->select('RUId as r_uid','EAccount as hx_id','NName as name','HeadUrl as head_img')
                ->rightJoin('RUserBase','UId','=','RUId')
                ->where('RUId',$f_uid)
                ->first();
            if ($rmap != false)
                $result[$key][]  = $rmap;
        }
        return response()->json(['status'=>1,'friend_list'=>$result]);
    }

    public function getRobotByHxId($hx_id){
        $result = \DB::table('RMapEase')->where('EAccount',$hx_id)->first();
        if ($result){
            $r_uid = $result->RUId;
            $robot = DB::table('RUserBase')
                ->select('UId as r_uid','NName as name','HeadUrl as head_img')
                ->where('UId',$r_uid)->first();
            return response()->json(['status'=>1,'r_info'=>$robot]);
        }

    }

}