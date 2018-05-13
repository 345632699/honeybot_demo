<?php
/**
 * Created by PhpStorm.
 * User: xu
 * Date: 2018/5/11
 * Time: 10:30
 */

namespace App\Http\Controllers\Robot;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(Request $request){
        $where = [];
        //1 朋友可见  2 广场圈
        if ($request->type == 1){
            $where['r_uid'] = $request->r_uid;
            $where['type'] = 1;
        }else{
            $where['type'] = 2;
        }
        $bool = isset($request->input()['limit']);
        if (!$bool){
            $limit = 10;
        }else{
            $limit = $request->limit;
        }
        $list = \DB::table('robot_article')
            ->select("robot_article.*","NName as name","HeadUrl as head_image")
            ->leftJoin("RUserBase",'r_uid','=','UId')
            ->where($where)
            ->paginate($limit);
        foreach ($list as $item){
            $item->created_at = strtotime($item->created_at);
            $item->updated_at = strtotime($item->updated_at);
            $item->like = intval($item->like);
        }
        return response()->json(['article_list'=>$list]);
    }

    public function createArticle(Request $request){
        try{
            $img_path = base_path('public/upload/images');
            $voice_path = base_path('public/upload/voice');
            if ($request->hasFile('image')){
                $name =  $request->file('image')->getClientOriginalName();
                $image = 'public/upload/images/img_'.time().$name;
                $request->file('image')->move($img_path,'img_'.time().$name);
            }
            if ($request->hasFile('voice')){
                $name =  $request->file('voice')->getClientOriginalName();
                $voice = 'public/upload/voice/voice_'.time().$name;
                $request->file('voice')->move($voice_path,'voice_'.time().$name);
            }
            if ($request->hasFile('thumbnail')){
                $name =  $request->file('thumbnail')->getClientOriginalName();
                $thumbnail = 'public/upload/images/thumbnail_'.time().$name;
                $request->file('thumbnail')->move($voice_path,'thumbnail_'.time().$name);
            }
            if (isset($request->input()['time_length'])){
                $time = $request->input()['time_length'];
            }

            if (isset($request->type))
                $input['type'] = $request->type;
            if (isset($request->r_uid))
                $input['r_uid'] = $request->r_uid;
            $input['image'] = isset($image) ? $image : null;
            $input['voice'] = isset($voice) ? $voice : null;
            $input['thumbnail'] = isset($thumbnail) ? $thumbnail : null;
            $input['time_length'] = isset($time) ? $time : null;
            $input['like'] = 0;

            $res = \DB::table('robot_article')->insertGetId($input);
            if ($res){
                $return['stauts'] = 1;
                $return['msg'] = "发表成功";
                $return['id'] = $res;
            }else{
                $return['stauts'] = 0;
                $return['msg'] = "发表失败";
                $return['id'] = 0;
            }
        }catch (\Exception $e){
            $return['stauts'] = 0;
            $return['err_msg'] = $e->getMessage();
            $return['id'] = 0;
        }
        return response()->json($return);
    }

    public function test(){
        return view('test');
    }
}