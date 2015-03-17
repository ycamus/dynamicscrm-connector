<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alan.hollis
 * Date: 04/07/13
 * Time: 11:00
 */


/**
 * Class TimeHelper
 *
 * @package connector\lib\Helper\Time
 */
class TimeHelper
{
    /**
     * @return string
     */
    public function getCurrentTime()
    {
        return substr(gmdate('c'), 0, -6) . ".00Z";
    }

    /**
     * @return string
     */
    public function getExpiryTime()
    {
        return substr(gmdate('c', strtotime('+1 minute')), 0, -6) . ".00Z";
    }
}
