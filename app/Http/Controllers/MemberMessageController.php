<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Providers\AASController;
use App\Http\Controllers\Providers\HABAController;
use App\Http\Controllers\Providers\PPController;
use App\Http\Controllers\Providers\WMController;
use App\Events\MessageNotification;

use DB;
use Auth;
use Log;

class MemberMessageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public static function createNewMsg(Request $request)
    {
        try
        {
            $subject = $request->input('subject');
            $message = $request->input('message');
            $id  = Auth::user()->id;

            //m = member 
            $type = 'm';

            if($subject == null)
            {
                $response = ['status' => 0
                            ,'error' =>  __('error.msg.insertsubject')
                            ];

                return json_encode($response);
            }

            if($message == null)
            {
                $response = ['status' => 0
                            ,'error' =>  __('error.msg.insertmsg')
                            ];

                return json_encode($response);
            }

            $dbMember = DB::select('SELECT id FROM member WHERE id = ? ',[$id]);

            if(sizeof($dbMember) == 0)
            {
                $response = ['status' => 0
                            ,'error' => __('error.msg.invalidmember')
                            ];

                return json_encode($response);
            }
            
            $insert = DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,subject,message,created_at)
                            VALUES(?,0,?,?,?,NOW())'
                            ,[ $id
                               ,$type
                               ,$subject
                               ,$message
                            ]);

            $response = ['status' => 1];
            return json_encode($response);

        }
        catch(\Exception $e)
        {

            log::debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.msg.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function inboxMsg(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');


            $user = Auth::user();
            $id = $user->id;

            /*self::updateUnreadMsg($memberId);*/


            $sql = "
                    SELECT b.username,a.id, a.member_id, a.is_read, a.subject, a.message,(a.created_at + INTERVAL 4 HOUR) 'created_at',a.send_by
                    FROM member_msg a
                    LEFT JOIN member b
                      ON a.member_id = b.id 
                    Where a.member_id = :id AND a.send_by = 'a' AND a.is_deleted IS NULL
                    ";

            $params = 
            [
                "id" => $id
            ];

            $orderByAllow = ['username','created_at'];
            $orderByDefault = 'created_at desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page);

            foreach($data['results'] as $d)
            {
                $d->send_by_desc = Helper::getOptionsValue(self::getOptionsSendBy(), $d->send_by);
            }

            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }

    public static function sentMsg(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');


            $user = Auth::user();
            $id = $user->id;

            // self::updateUnreadMsg($id);

            $sql = "
                    SELECT b.username,a.id,a.member_id,a.subject,a.message,(a.created_at + INTERVAL 4 HOUR) 'created_at',a.send_by
                    FROM member_msg a
                    LEFT JOIN member b
                      ON a.member_id = b.id 
                    Where a.member_id = :id AND a.send_by = 'm' AND a.is_deleted IS NULL
                    ";

            $params = 
            [
                "id" => $id
            ];

            $orderByAllow = ['username','created_at'];
            $orderByDefault = 'created_at desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page);

            foreach($data['results'] as $d)
            {
                $d->send_by_desc = Helper::getOptionsValue(self::getOptionsSendBy(), $d->send_by);
            }

            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }

    public static function updateUnreadMsg(Request $request)
    {
        try
        {
            $msgId = $request['msgId'];

            $user = Auth::user();
            $memberId = $user->id;
            
            if(is_array($msgId) == 1)
            {
                foreach($msgId as $id)
                {
                    $db = DB::select('SELECT id FROM member_msg WHERE id = ? AND member_id = ?',[$id,$memberId]);

                    //prevent by pass message id 
                    if(sizeof($db) > 0)
                    {
                        $db = DB::update('UPDATE member_msg
                            SET is_read = ?
                            WHERE member_id = ? AND id = ? AND send_by = "a"
                            ',[1,$memberId,$id]
                        );
                    }
                }

            }
            else
            {
                $db = DB::update('UPDATE member_msg
                        SET is_read = ?
                        WHERE member_id = ? AND id = ? AND send_by = "a"
                        ',[1,$memberId,$msgId]
                    );
            }


            self::sendWs();


        }
        catch(\Exception $e)
        {
            Log::Debug($e);
        }
    }

    public static function getUnreadMsg()
    {
        try
        {
            $userId = Auth::id();

            $db = DB::select("
                    SELECT count(*) count
                    FROM member_msg
                    WHERE member_id = ?
                      AND send_by = 'a' 
                      AND is_deleted IS NULL
                      AND is_read = 0
                    ",[$userId]
                );

            return $db[0]->count;
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return 0;
        }
    }


    public static function sendWs()
    {
        try
        {

            $userId = Auth::id();
            //get member unread message 
            $db = DB::select('
                SELECT count(id) "count"
                FROM member_msg
                WHERE member_id = ? 
                 AND send_by = "a"
                 AND is_read =0
                ',[$userId]
            );

            //get member username 
            $db1 = DB::select('
                    SELECT username
                    FROM member
                    WHERE id = ?'
                    ,[$userId]
                );


            //send ws 
            event(new MessageNotification($db1[0]->username,$db[0]->count));

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
        }
    }


    public static function getOptionsSendBy()
    {

        return  [
             ['m', 'Member']
            ,['a', 'Agent']
        ];
    }


}
