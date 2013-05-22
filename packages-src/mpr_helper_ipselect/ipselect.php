<?php
namespace mpr\helper;

/**
 * Look up local ip list
 *
 * @author Petr Demin <demin@infostars.ru>
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class ipselect
{
    /**
     * @var string $ip_select_command - shell command
     */
    private static $ip_select_command = <<<CMD
/sbin/ip a | grep "inet " | awk '{print $2}' | cut -d "/" -f 1 | grep -v '127.0.0'
CMD;

    /**
     * Return ip array on local machine
     *
     * @return array
     */
    public static function getList()
    {
        static $network_interfaces_list;

        if($network_interfaces_list == null) {
            $exec = self::$ip_select_command;
            $network_interfaces_string = trim(shell_exec($exec));
            if(strlen($network_interfaces_string) > 0) {
                $network_interfaces_list = explode("\n", $network_interfaces_string);
            } else {
                $network_interfaces_list = [];
            }
        }

        return $network_interfaces_list;
    }

    /**
     * Return ip count on local machine
     *
     * @return int Count
     */
    public static function getCount()
    {
        static $count;

        if($count == null) {
            $exec = self::$ip_select_command . " -c";
            $count = intval(trim(shell_exec($exec)));
        }

        return $count;
    }
}