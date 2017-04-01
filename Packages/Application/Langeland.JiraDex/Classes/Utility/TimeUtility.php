<?php
/**
 * Created by PhpStorm.
 * User: langeland
 * Date: 15/03/2017
 * Time: 17.05
 */

namespace Langeland\JiraDex\Utility;

class TimeUtility
{

    public static function format($seconds)
    {
        $t = round($seconds);
        return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
    }
}