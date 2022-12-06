<?php

namespace App\Http\Controllers;

use Log;
use DB;

class Providers
{
    //fast game
    const FastGame = 5;

    //GS
    const Gameplay = 1; //casino
    const BBIN = 2;// sportbook/lottery
    // const IBC = 3;//sportbook
    const ALLBET = 4;//sportbook
    const CQ9 = 6;//slot(lobby)
    const WM = 7;//casino
    // const Joker = 8;//fishing game/slot(lobby)
    const PSB4D = 9;//lottery
    const Spade = 10;//fishing game/slot(lobby)
    const QQKeno = 11;//lottery
    const CMD = 12;//sportbook
    const M8BET = 13;//sportbook
    const DIGMAAN = 14;//other
    const EBET = 15;//casino
    const IA = 16;//e-sport
    const NLIVE22 = 17;//casino

    //ps9 casino
    const Ps9EVO = 101;
    const Ps9AG = 102;
    const Ps9PP = 103;
    const Ps9OT = 104;
    const Ps9IA = 105;
    //ps9 slot
    const Ps9PPSlot = 106;
    const Ps9Haba = 107;
    const Ps9Ely = 108;
    const Ps9QS = 109;
    const Ps9SG = 110;
    const Ps9AWS = 111;//not support MYR
    const Ps9PnG = 112;
    const Ps9WM = 113;
    const Ps9Micro = 114;//not support MYR
    const Ps9Joker = 115;//not support MYR
    const Ps9OTSlot = 116;
    const Ps9EvoRtg = 117;
    const Ps9Netent = 118;
    const Ps9Booon = 119;
    const Ps9Playson = 120;
    const Ps9PS = 121;//not support MYR

    //external apps
    // const MEGA = 200;
    // const NOE = 201;
    // const PUSSY = 202;

    //new
    const SBO = 1000;
    const EVO = 1001;
    const IBC = 1002;
    const SA = 1003;
    const AB = 1004;
    const PT = 1005;
    const Joker = 1006;
    const XE88 = 1007;
    const SexyGaming = 1012;

    //external apps
    const MEGA = 1008;
    const NOE = 1009;
    const SCR = 1010;
    const PUSSY = 1011;
    const KAYA = 1013;

    const LIVEevoC = 2000;//Evolution Gaming live casino (2)
    const LIVEabC = 2001;//All Bet live casino (2)
    const LIVEbgC = 2002;//Big Gaming live casino (2)
    const LIVEsaC = 2003;//SA Gaming live casino (2)
    const LIVEppS = 2004;//Pragmatic Play slot (1)
    const LIVEpgsS = 2005;//PG Soft live slot (1)
    const LIVEcq9S = 2006;//CQ9 slot (1)
    const LIVEcq9F = 2007;//CQ9 fishing (8)
    const LIVEptS = 2008;//Play Tech slot (1)
    const LIVEptC = 2009;//Play Tech live casino (2)
    const LIVEjokerS = 2010;//Joker slot (1)
    const LIVEdsS = 2011;//Dragon Soft slot (1)
    const LIVEtfS = 2012;//TF Gaming slot (1)
    const LIVEtfE = 2013;//TF Gaming esport (13)
    const LIVEwmC = 2014;//WM Casino live casino (2)
    const LIVEsgC = 2015;//Sexy Gaming live casino (2)
    const LIVEkingC = 2016;//King 855 live casino (2)
    const LIVEamayaS = 2017;//AMAYA slot (1)
    const LIVEhabaS = 2018;//Habanero slot (1)
    const LIVEibcSB = 2019;//IBC sport book (3)
    const LIVEreevoS = 2020;//Reevo     slot (1)
    const LIVEevopS = 2021;//EvoPlay   slot (1)
    const LIVEpsS = 2022;//PlayStar  slot (1)
    const LIVEdgC = 2023;//Dream Gaming  live casino (2)
    const LIVEnexusL = 2024;//Nexus 4D  lottery (5)
    const LIVEhkgpL = 2025;//HKGP Lottery  lottery (5)
    const LIVEslotxoS = 2026;//SlotXo    slot (1)
    const LIVEambP = 2027;//AMB Poker     p2p (7)
    const LIVEskyS = 2028;//SkyWind   slot (1)
    const LIVEskyC = 2029;//SkyWind   live casino (2)
    const LIVEbtiSB = 2030;//BTI   sport book (3)
    const LIVEapS = 2031;//Advant Play   slot (1)
    const LIVEjdbS = 2032;//JDB   slot (1)
    const LIVEjiliS = 2033;//Jili  slot (1)
    const LIVEjiliF = 2034;//Jili  fishing (8)

    // public static function getProductLogTable($prdId)
    // {   
    //     try
    //     {  
    //         if ($prdId == static::Kiron) 
    //         {
    //             $db = "log_kiron";
    //         }
    //         else if ($prdId == static::SportsBook) 
    //         {
    //             $db = "log_sportsbook";
    //         }
    //         else if ($prdId == static::MiniGame) 
    //         {
    //             $db = "log_minigame";
    //         }

    //         return ['db' => $db];
            
    //     } 
    //     catch(\Exception $e)
    //     {
    //         log::debug($e);
    //         return false;
    //     }
    // }

    public static function isValidProduct($prdId)
    {   
        try
        {  
            //in future need enhancement, including the const defined above.
            $productList = [
                static::Gameplay
                ,static::BBIN
            ];  

           return in_array($prdId, $productList);
        } 
        catch(\Exception $e)
        {
            log::debug($e);
            return false;
        }
    }

    public static function isProductEnable($prdId,$merchantCode)
    {   
        try
        {  
            $db = DB::select('SELECT status
                                FROM product_setting
                                WHERE prd_id = ?
                                    AND merc_cd = ?'
                                    ,[$prdId,$merchantCode]);

           if ($db[0]->status == 1) 
           {
                return true;
           }
           else
           {
                return false;
           }
        } 
        catch(\Exception $e)
        {
            log::debug($e);
            return false;
        }
    }

    //ps9 map to own prdId
    public static function mapPS9Game()
    {
        $game = array(
                    //casino
                    Providers::Ps9EVO  => 1
                    ,Providers::Ps9AG  => 5
                    ,Providers::Ps9PP  => 10
                    ,Providers::Ps9OT  => 11
                    ,Providers::Ps9IA  => 100
                    //slot
                    ,Providers::Ps9PPSlot  => 200
                    ,Providers::Ps9Haba  => 201
                    ,Providers::Ps9Ely  => 202
                    ,Providers::Ps9QS  => 204
                    ,Providers::Ps9SG  => 205
                    ,Providers::Ps9AWS  => 206
                    ,Providers::Ps9PnG  => 207
                    ,Providers::Ps9WM  => 208
                    ,Providers::Ps9Micro  => 209
                    ,Providers::Ps9Joker  => 210
                    ,Providers::Ps9OTSlot  => 211
                    ,Providers::Ps9EvoRtg  => 213
                    ,Providers::Ps9Netent  => 214
                    ,Providers::Ps9Booon  => 217
                    ,Providers::Ps9Playson  => 218
                    ,Providers::Ps9PS  => 219
                );

        return $game;
    }

    public static function mapPS9PicFolder()
    {
        $pic = array(
                    //casino
                    Providers::Ps9EVO  => 1
                    ,Providers::Ps9AG  => 5
                    ,Providers::Ps9PP  => 10
                    ,Providers::Ps9OT  => 11
                    ,Providers::Ps9IA  => 100
                    //slot
                    ,Providers::Ps9PPSlot  => 'Pragmatic'
                    ,Providers::Ps9Haba  => 'Habanero'
                    ,Providers::Ps9Ely  => 'Elysium'
                    ,Providers::Ps9QS  => 'Quickspin'
                    ,Providers::Ps9SG  => 'Spade Gaming'
                    ,Providers::Ps9AWS  => 206
                    ,Providers::Ps9PnG  => 'Play N Go'
                    ,Providers::Ps9WM  => 'World Match'
                    ,Providers::Ps9Micro  => 209
                    ,Providers::Ps9Joker  => 210
                    ,Providers::Ps9OTSlot  => 'One Touch'
                    ,Providers::Ps9EvoRtg  => 213
                    ,Providers::Ps9Netent  => 'Netent'
                    ,Providers::Ps9Booon  => 'Booongo'
                    ,Providers::Ps9Playson  => 'Playson'
                    ,Providers::Ps9PS  => 219
                );

        return $pic;
    }

    // public static function isProductEnableByMember($prdId,$memberId)
    // {   
    //     try
    //     {  
    //         $db = DB::select('SELECT status
    //                             FROM product_setting b
    //                             INNER JOIN member a 
    //                             on a.ag_code = b.tier_code
    //                             WHERE prd_id = ?
    //                                 AND id = ?'
    //                                 ,[$prdId,$memberId]);

    //        if ($db[0]->status == 1) 
    //        {
    //             return true;
    //        }
    //        else
    //        {
    //             return false;
    //        }
    //     } 
    //     catch(\Exception $e)
    //     {
    //         log::debug($e);
    //         return false;
    //     }
    // }
}
