<?php
namespace mpr;

use \mpr\debug\log;

class time
{

    private $date = 0;

    private $langPack = [
        'dictionary' => [
            'now' => 'сейчас',
            'second' => [
                0 => 'секунда',
                1 => 'секунды',
                2 => 'секунду',
                3 => 'секунд'
            ],
            'minute' => [
                0 => 'минута',
                1 => 'минуты',
                2 => 'минуту',
                3 => 'минут'
            ],
            'hour' => [
                0 => 'час',
                1 => 'часа',
                2 => 'час',
                3 => 'часов'
            ],
            'day' => [
                0 => 'день',
                1 => 'дня',
                2 => 'день',
                3 => 'дней'
            ],
            'month' => [
                0 => 'месяц',
                1 => 'месяца',
                2 => 'месяц',
                3 => 'месяцев'
            ],
            'year' => [
                0 => 'год',
                1 => 'года',
                2 => 'год',
                3 => 'лет',
            ]
        ],
        'format' => [
            'real' => [
                'string' => '{$TIME}',
                'date_format' => '{$T} {$W}',
                'concat' => ', ',
                'usage' => [
                    0 => 3,
                    1 => 0,
                    2 => 1,
                    3 => 1,
                    4 => 1,
                    5 => 3,
                    6 => 3,
                    7 => 3,
                    8 => 3,
                    9 => 3
                ]
            ],
            'past' => [
                'string' => '{$TIME} назад',
                'date_format' => '{$T} {$W}',
                'concat' => ', ',
                'usage' => [
                    0 => 3,
                    1 => 2,
                    2 => 1,
                    3 => 1,
                    4 => 1,
                    5 => 3,
                    6 => 3,
                    7 => 3,
                    8 => 3,
                    9 => 3
                ]
            ],
            'future' => [
                'string' => 'через {$TIME}',
                'date_format' => '{$T} {$W}',
                'concat' => ', ',
                'usage' => [
                    0 => 3,
                    1 => 2,
                    2 => 1,
                    3 => 1,
                    4 => 1,
                    5 => 3,
                    6 => 3,
                    7 => 3,
                    8 => 3,
                    9 => 3
                ]
            ]
        ],
        'additional' => [
            'format' => [
                'day' => [
                    '{$DIFF} > 0 && {$DIFF} < 86400' => 'сегодня',
                    '{$DIFF} >= 86400 && {$DIFF} < 86400*2' => 'завтра',
                    '{$DIFF} < 0 && {$DIFF} > 86400' => 'вчера',
                ]
            ]
        ]
    ];

    const DATE_FORMAT = "YmdHis";

    const HUMAN_TYPE_DIFF = false;

    const HUMAN_TYPE_PAST = 'past';

    const HUMAN_TYPE_FUTURE = 'future';

    const HUMAN_TYPE_REAL = 'real';

    public function __construct($sometime = false)
    {
        $timestamp = $this->parseStr($sometime);
        if($timestamp === false) {
            if($this->normalizeSeconds($sometime)) {
                $timestamp = $sometime;
            } else {
                $timestamp = time();
            }
        }
        $this->setSeconds($timestamp);
    }

    protected function normalizeSeconds(&$input)
    {
        log::put("Normalizing time `{$input}`", config::getPackageName(__CLASS__));
        $timestamp = intval($input);
        if($timestamp === false) {
            log::put("Time normalization for `{$input}` FAILED!", config::getPackageName(__CLASS__));
            return false;
        }
        log::put("Time normalization for `{$input}` SUCCESS!", config::getPackageName(__CLASS__));
        $input = $timestamp;
        return true;
    }

    public function setSeconds($seconds)
    {
        log::put("Set seconds `{$seconds}`", config::getPackageName(__CLASS__));
        $this->date = date(self::DATE_FORMAT, $seconds);
        return true;
    }

    protected function parseStr($date)
    {
        return strtotime($date);
    }

    public function getSeconds()
    {
        return strtotime($this->date);
    }

    public function getDatetime()
    {
        return $this->date;
    }

    public function getDate($format = "Y-m-d H:i:s")
    {
        return date($format, $this->getSeconds());
    }

    public function setLangPack($langPack)
    {
        $this->langPack = $langPack;
    }

    public function getLangPack()
    {
        return $this->langPack;
    }

    public function getHumanReadable($time_relation = self::HUMAN_TYPE_DIFF, $useWeeks = false)
    {
        $timestamp = $this->getSeconds();

        $periods = [
            "year" => 31104000,
            "month" => 2592000,
            "day" => 86400,
            "hour" => 3600,
            "minute" => 60,
            "second" => 1
        ];

        $result = [
            'timestamp' => $timestamp,
            'difference' => 0,
            'data' => [
                'year' => 0,
                'month' => 0,
                'day' => 0,
                'hour' => 0,
                'minute' => 0,
                'second' => 0,
            ]
        ];

        if($time_relation == self::HUMAN_TYPE_DIFF) {
            $result['difference'] = $timestamp - time();
            if($result['difference'] > 0) {
                $time_relation = self::HUMAN_TYPE_FUTURE;
            } elseif($result['difference'] < 0) {
                $time_relation = self::HUMAN_TYPE_PAST;
                $result['difference'] = -$result['difference'];
            } else {
                $time_relation = self::HUMAN_TYPE_REAL;
            }
        } elseif($time_relation == self::HUMAN_TYPE_FUTURE) {
            $result['difference'] = $timestamp;
        } elseif($time_relation == self::HUMAN_TYPE_PAST) {
            $result['difference'] = -$timestamp;
        } else {
            $time_relation = self::HUMAN_TYPE_REAL;
            $result['difference'] = $timestamp;
        }

        $remain = $result['difference'];
        foreach($periods as $period_name => $period_length) {

            $result['data'][$period_name] = (int)($remain / $period_length);
            $remain -= $result['data'][$period_name] * $period_length;
        }

        $output_array = [];

        $dictionary = $this->langPack['dictionary'];
        $format = $this->langPack['format'][$time_relation];

        foreach($result['data'] as $period_name => $period_length) {
            $value = $result['data'][$period_name];
            $lastNumber = intval(substr($value, -1));
            if($value != 0) {
                $replacement = [
                    '/{\\$T}/' => $value,
                    '/{\\$W}/' => $dictionary[$period_name][$format['usage'][$lastNumber]]
                ];
                $output_array[] = preg_replace(array_keys($replacement), array_values($replacement), $format['date_format']);
            }
        }

        if(count($output_array)) {
            $timevar = implode($format['concat'], $output_array);
            $output = preg_replace('/{\\$TIME}/', $timevar, $format['string']);
        } else {
            $output = $dictionary['now'];
        }

        return $output;
    }

}