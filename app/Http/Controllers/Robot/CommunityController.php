<?php
/**
 * Created by PhpStorm.
 * User: xu
 * Date: 2018/5/11
 * Time: 10:30
 */

namespace App\Http\Controllers\Robot;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(Request $request){
        $input = $request->input();
        $where = [];
        $sort = 'asc';
        $act = isset($input['act']) ? $input['act'] : ">";
        $offset = isset($input['offset']) ? $input['offset'] : 0;
        $type = isset($input['type']) ? $input['type'] : 2;
        $r_uid = isset($input['r_uid']) ? $input['r_uid'] : 0;
        //1 朋友可见  2 广场圈
        if ($type == 1 && $r_uid > 0){
            $where['r_uid'] = $r_uid;
            $where['type'] = 1;
        }else{
            $where['type'] = 2;
        }
        $bool = isset($input['limit']);
        if (!$bool){
            $limit = 8;
        }else{
            $limit = $request->limit;
        }

        if ($offset == -1){
            $offset = 0;
            $sort = "desc";
        }

        if ($offset > 0 && $act == "pre"){
            $act = "<";
            $sort = "desc";
        }else{
            $act = ">";
        }

        $list = \DB::table('robot_article')
            ->select("robot_article.*","NName as name","HeadUrl as head_image")
            ->leftJoin("RUserBase",'r_uid','=','UId')
            ->where($where)
            ->where('id',$act,$offset)
            ->limit($limit)
            ->orderBy('id',$sort)
            ->get();
        foreach ($list as $item){
            $item->created_at = intval(strtotime($item->created_at));
            $item->updated_at = intval(strtotime($item->updated_at));
            $item->like = intval($item->like);
            $item->time_length = intval($item->time_length);
        }
        $data['list'] = $list;
        $data['total'] = $list->count();
        $data['limit'] = intval($limit);
        return response()->json($data);
    }

    public function createArticle(Request $request){
        try{
            $img_path = base_path('public/upload/images');
            $voice_path = base_path('public/upload/voice');
            if ($request->hasFile('image')){
                $name =  $request->file('image')->getClientOriginalName();
                $ext =  $request->file('image')->getClientOriginalExtension();
                $image = 'upload/images/img_'.time().$name;
                $request->file('image')->move($img_path,'img_'.time().$name.".".$ext);
            }
            if ($request->hasFile('voice')){
                $name =  $request->file('voice')->getClientOriginalName();
                $ext =  $request->file('voice')->getClientOriginalExtension();
                $voice = 'upload/voice/voice_'.time().$name;
                $request->file('voice')->move($voice_path,'voice_'.time().$name.".".$ext);
            }
            if ($request->hasFile('thumbnail')){
                $name =  $request->file('thumbnail')->getClientOriginalName();
                $ext =  $request->file('thumbnail')->getClientOriginalExtension();
                $thumbnail = 'upload/images/thumbnail_'.time().$name;
                $request->file('thumbnail')->move($img_path,'thumbnail_'.time().$name.".".$ext);
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
            $input['created_at'] = Carbon::now();
            $input['updated_at'] = Carbon::now();
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