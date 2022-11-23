<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ProviderControllers\Provider;
use App\Http\Controllers\MemberController;
use Log;

class Helper
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public static function getData($url,$header = '')
    {
        try
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            
            if($header == '')
            {
                $header = array('Content-Type: application/json');
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function postData($url,$data,$header = '')
    {
        try
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);

            if($header == '')
            {
                $header = array('Content-Type: application/json');
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            if (is_array($data))
            {
                $data = json_encode($data);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            return $response;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function logAPI($type,$content) 
    {
        //logging for debug
        $db = DB::insert('
            INSERT INTO log_json 
            (type,content)
            VALUES
            (?,?)'
            ,[$type,$content]);
    }

    public static function formatMoney($money)
    {
        return number_format($money, 2);
    }

    public static function generateUniqueId($length = 64)
    {
        //minimum length 64

        $length = $length < 64 ? 64 : $length;

        $str = uniqid('',true); //23 char
        $str = md5($str); //32 char

        $str = self::generateRandomString($length - 32).$str;
        return $str;
    }
    
    public static function generateRandomString($length = 1) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) 
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getOptionsValue($aryOptions,$value)
    {
        foreach ($aryOptions as $option) 
        {
            if($option[0] == $value)
                return $option[1];
        }

        return '';
    }

    public static function checkValidOptions($aryOptions,$value)
    {
        foreach ($aryOptions as $option) 
        {
            if($option[0] == $value)
                return true;
        }

        return false;
    }

    public static function appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault = '')
    {
        $orderTypeAllow = ['asc','desc'];

        $strOrder = '';

        if(in_array($orderBy,$orderByAllow))
        {
            if(in_array($orderType,$orderTypeAllow))
            {
                $strOrder = ' '.$orderBy.' '.$orderType;

            }
        }

        if($strOrder == '')
            $strOrder = $orderByDefault;

        if($strOrder != '')
            $strOrder = ' ORDER BY '.$strOrder;

        return $sql.$strOrder;
    }

    public static function paginateData($sql,$params,$page,$pageSize=0)
    {
        //pageNo = index 1-based
        //params :pagination_row and :pagination_size : reserved

        if($page == null)
            $page = 1;

        if($pageSize==0)
            $pageSize = env('GRID_PAGESIZE');

        //get data count
        $sqlCount = "SELECT COUNT(0) AS count FROM (".$sql.") AS a";
        $dbCount = DB::select($sqlCount,$params);

        //get data
        $sqlData = $sql." LIMIT :pagination_row,:pagination_size";

        $params['pagination_row'] = (($page - 1) * $pageSize);
        $params['pagination_size'] = $pageSize;

        $dbData = DB::select($sqlData,$params);

        $data = ['count' => $dbCount[0]->count,'page_size' => $pageSize,'results' => $dbData];

        return $data; 
    }

    public static function generateOptions($aryOptions,$default)
    {
        foreach ($aryOptions as $option) 
        {
            $selected = '';

            if($option[0] == $default)
                $selected = 'selected';

            echo '<option value="'.$option[0].'" '.$selected.'>'.$option[1].'</option>';
        }
    }

    public static function generateList($aryOptions,$default)
    {
        foreach ($aryOptions as $option) 
        {
            $selected = '';

            if($option[0] == $default)
                $selected = 'selected';

            echo '<a class="dropdown-item" value="'.$option[0].'" '.$selected.'>'.$option[1].'</a>';
        }
    }

    // bulk insert
    public static function prepareBulkInsert($sql,$aryParams)
    {
        //reserved keyword :( and ):

        try
        {
            $returnSQL = '';
            $returnParams = [];

            $valueStart = self::strposOffset(':(', $sql, 1);
            $valueEnd = self::strposOffset('):', $sql, 1);

            $value = substr($sql,$valueStart + 1,$valueEnd - $valueStart);

            $values = str_repeat(','.$value, count($aryParams));
            $values = ltrim($values,',');

            $returnSQL = substr_replace($sql,$values,$valueStart,$valueEnd - $valueStart + 2);

            foreach ($aryParams as $params) 
            {
                foreach ($params as $param) 
                {
                    array_push($returnParams,$param);
                }
            }

            return ['sql' => $returnSQL,'params' => $returnParams];
        }
        catch (Exception $e) 
        {
            return [];
        }
    }

    public static function prepareWhereIn($sql,$params)
    {
        $returnSql = $sql;
        $returnParams = [];

        $paramCount = 0;

        for($i = 0 ; $i < sizeOf($params) ; $i++)
        {
            if(is_array($params[$i]))
            {
                $explodeParams = str_repeat('?, ', count($params[$i]));
                $explodeParams = rtrim($explodeParams, ', ');

                $pos = self::strposOffset('?', $returnSql, $paramCount + 1);
                
                $returnSql = substr_replace($returnSql,$explodeParams,$pos,1);
                
                for($j = 0 ; $j < sizeOf($params[$i]) ; $j++)
                {
                    array_push($returnParams,$params[$i][$j]);
                    $paramCount++;
                }
            }
            else
            {
                array_push($returnParams,$params[$i]);
                $paramCount++;
            }
        }

        return ['sql' => $returnSql , 'params' => $returnParams];
    }

    public static function strposOffset($search, $string, $offset)
    {
        $arr = explode($search, $string);

        switch($offset)
        {
            case $offset == 0:
            return false;
            break;
        
            case $offset > max(array_keys($arr)):
            return false;
            break;

            default:
            return strlen(implode($search, array_slice($arr, 0, $offset)));
        }
    }

    public static function checkInputFormat($type, $data)
    {
        //alphanumeric format
        if($type=='alphanumeric')
        {
            if(preg_match('/[^a-zA-Z0-9]/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        //alphabet format
        if($type=='alphabet')
        {
            if(preg_match('/[^a-zA-Z]/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        //alphabet with space format
        if($type=='alphabetWithSpace')
        {
            if (preg_match('/[0-9\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $data)) 
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        //alphanumeric With Dot format
        if($type=='alphanumericWithDot')
        {
            if(preg_match('/[^a-zA-Z\.0-9]/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        //amount format
        if($type=='amount')
        {
            if(!preg_match('/^\\d+(\\.\\d{1,2})?$/D',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        } 
        //numeric format
        if($type=='numeric')
        {
            if(!preg_match('/^[1-9][0-9]*$/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }                 
    }

    public static function checkInputLength($data, $min, $max)
    {
        if(strlen($data)<$min || strlen($data)>$max)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function validAmount($money)
    {
        if(strlen($money) > 15)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function storeTxnCredit($refId,$prdId,$memberId,$beforeFrom,$amount)
    {
        //remove unwanted precision without rounding

        try
        {
            $amount = -$amount;
            $type = ($amount>0)?2:3;
           
            //txn type for transfer
            $txnType = 3;
            $sql = "
                    INSERT INTO member_credit_txn (ref_id,prd_id,type,member_id,credit_before,txn_type,amount)
                    VALUES  (:refid,:prdId,:type,:member,:before,:txnType,:amount)
                    ";

            $params = [

                    'refid' => $refId
                    ,'prdId' => $prdId
                    ,'type' => $type
                    ,'member' => $memberId
                    ,'before' => $beforeFrom
                    ,'txnType' => $txnType
                    ,'amount' => $amount

                ];

            DB::insert($sql,$params);
        }
        catch(\Exception $e)
        {}
    }

}
