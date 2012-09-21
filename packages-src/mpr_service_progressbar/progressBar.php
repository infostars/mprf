<?php
namespace mpr\service;

/**
 * consoleTimer: Таймер и прогрессбар для консольных приложений
 *
 * @package system
 * @subpackage service
 * @version 0.7
 * @author GreeveX <greevex@gmail.com>
 */
class progressBar
{

    /**
     * Общее количество данных
     *
     * @var int
     */
    protected $data_counter = 0;

    /**
     * Длина прогресс бара для прорисовки
     *
     * @var int
     */
    protected $pb_width = 60;

    /**
     * Время начала отсчета
     *
     * @var float
     */
    protected $time_start = 0;

    /**
     * Время последнего обновления
     *
     * @var float
     */
    protected $last_update = 0;

    /**
     * Сколько времени прошло с начала работы
     *
     * @var float
     */
    protected $time_elapsed = 0;

    /**
     * Сколько процентов обработки уже закончено
     *
     * @var float
     */
    protected $stat_percent = 0;

    /**
     * Сколько секунд осталось ждать
     *
     * @var float
     */
    protected $stat_estimatedTime = 0;

    protected $output_format = '[{progress}] | {done}% | {speed} p/s | te {time} | r {ram}';

    /**
     * Construct method
     * Как входящий параметр вносим общее количество данных по которым у нас будет проходить обработка
     *
     * @param int $data_counter - Сколько всего данных будет
     * @param int $pb_width - Длина прогрессбара
     * @return consoleTimer
     */
    public function __construct($data_counter, $pb_width = 60) {
        $this->time_start = microtime(true);
        $this->last_update = $this->time_start;
        $this->data_counter = $data_counter;
        $this->speed = 0;
        $this->pb_width = $pb_width;
    }

    /**
     * Обновление данных у таймера для рассчета прогрессбара и скорости
     *
     * @param int $curr_element - сколько элементов уже прошло
     */
    public function update($curr_element) {
        if($this->data_counter < 1) {
            $this->data_counter = 1;
        }
        $this->time_elapsed = microtime(true) - $this->time_start;
        $this->stat_percent = round((($curr_element * 100) / $this->data_counter), 3);
        $this->speed = $curr_element / $this->time_elapsed;
        $this->stat_estimatedTime = ($this->data_counter - $curr_element) / $this->speed;
        $this->last_update = microtime(true);
    }

    /**
     * Получение рассчетных данных для предоставления пользователю
     * Ключи массива:
     *   done => (float)процент завершенности
     *   time_elapsed => (float unix_timestamp)прошедшее время
     *   time_estimated => (float unix_timestamp)примерное время до окончания работы
     *   speed => (float)количество обрабатываемых элементов в секунду
     *
     * @return array
     */
    public function getData() {
        return array(
            'done' => number_format($this->stat_percent, 2),
            'time_elapsed' => $this->time_elapsed,
            'time_estimated' => $this->stat_estimatedTime,
            'speed' => number_format($this->speed, 2),
        );
    }

    /**
     * Рисуем прогрессбар в консоли, не должно быть переносов строк между "кадрами"!
     * Перед каждым новым "кадром" нужно вызывать обновление для получения актуальной информации
     */
    public function draw() {
        $data = $this->getData();
        if ($data['done'] > 100) {
            $data['done'] = 100;
        }
        $done = ceil($this->pb_width / 100 * $data['done']);
        if ($done < 1) {
            $done = 1;
        }
        $search = array(
            '{progress}',
            '{done}',
            '{speed}',
            '{time}',
            '{ram}'
        );
        $replace = array(
            (str_repeat('=', $done - 1) . ">" . str_repeat(' ', $this->pb_width - ($done - 1))),
            $data['done'],
            $data['speed'],
            date("i:s", $data['time_estimated']),
            round(memory_get_usage(true)/1024/1024, 4)
        );
        $string = str_replace($search, $replace, $this->output_format);
        \grunge\system\systemToolkit::getInstance()
                ->getResponse()
                ->write("\r{$string}");
    }

    public function setOutputFormat($string)
    {
        $this->output_format = $string;
    }

}