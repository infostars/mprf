<?php
namespace mpr\collections;

/**
 * Реализация класса List из .NET 4.0
 *
 * @property int $Count Возвращает число элементов, которые фактически содержатся в коллекции
 * @property mixed $Item Получает объект с указанным индексом
 *
 * @url: http://msdn.microsoft.com/ru-ru/library/6sh2ey19.aspx
 * @author: greevex <greevex@gmail.com>
 */
class arrayList
extends \ArrayObject
{
    /**
     * Возвращает или задает общее число элементов,
     * которые может вместить внутренняя структура данных без изменения размера
     *
     * @var int
     */
    public $Capacity = PHP_INT_MAX;

    public function __get($property)
    {
        switch($property) {
            case 'Count':
                return $this->count();
            case 'Item':
                return $this->getIterator()->current();
        }
    }

    public function __construct($input = null)
    {
        if($input != null) {
            if(is_array($input)) {
                parent::__construct($input);
            } elseif(is_int($input)) {
                $this->Capacity = $input;
                for($key = 0; $key < $input; $key++) {
                    $this[$key] = null;
                }
            }
        }
    }

    /**
     * Добавляет объект в конец коллекции
     *
     * @param mixed $object
     */
    public function Add($object)
    {
        $this[] = $object;
    }

    /**
     * Добавляет элементы указанной коллекции в конец списка
     *
     * @param array $data
     */
    public function AddRange($data)
    {
        foreach ($data as $value) {
            $this[] = $value;
        }
    }

    /**
     * Удаляет все элементы из коллекции
     */
    public function Clear()
    {
        $this->exchangeArray(array());
    }

    /**
     * Определяет, входит ли элемент в состав коллекции
     *
     * @param $object
     * @return bool
     */
    public function Contains($object)
    {
        return array_search($object, $this->ToArray()) !== false;
    }

    /**
     * Копирует весь список коллекции в совместимый одномерный массив, начиная с первого элемента конечного массива
     * или начиная с указанного индекса конечного массива.
     *
     * @param array $array
     * @param int $offset
     */
    public function CopyTo(&$array, $offset = 0)
    {
        foreach($this as $value) {
            $array[$offset] = $value;
            $offset++;
        }
    }

    /**
     * Определяет, равен ли заданный объект object текущему объекту object.
     *
     * @param $object
     * @return bool
     */
    public function Equals($object)
    {
        return ($this == $object);
    }

    /**
     * Выполняет указанное действие с каждым элементом списка
     *
     * @param $closure
     */
    public function Each($closure)
    {
        array_walk($this, $closure);
    }

    /**
     * Создает копию диапазона элементов исходного списка
     *
     * @param int $index
     * @param int $count
     * @return array
     */
    public function GetRange($index = 0, $count = null) {
        return array_slice($this->ToArray(), $index, $count);
    }

    /**
     * Осуществляет поиск указанного объекта и возвращает отсчитываемый от нуля индекс первого вхождения
     * в диапазоне элементов списка, начиная с заданного индекса и до последнего элемента
     *
     * @param mixed $object
     * @return int
     */
    public function IndexOf($object) {
        return array_shift(array_keys($this->ToArray(), $object));
    }

    /**
     * Добавляет элемент в список с указанным индексом
     *
     * @param int $index
     * @param mixed $object
     */
    public function Insert($index, $object) {
        $this[$index] = $object;
    }

    /**
     * Вставляет элементы коллекции в список в позиции начиная с указанного индекса
     *
     * @param int $index
     * @param array $range
     */
    public function InsertRange($index, $range) {
        foreach($range as $value) {
            $this[$index] = $value;
            $index++;
        }
    }

    /**
     * Удаляет первое вхождение указанного объекта из коллекции
     *
     * @param $object
     */
    public function Remove($object)
    {
        unset($this[$this->IndexOf($object)]);
    }

    /**
     * Удаляет элемент списка с указанным индексом
     *
     * @param int $index
     */
    public function RemoveAt($index)
    {
        unset($this[$index]);
    }

    /**
     * Удаляет диапазон элементов из списка
     *
     * @param int $index
     * @param int $count
     */
    public function RemoveRange($index, $count)
    {
        for($key = $index; $key < $count; $key++) {
            $this->RemoveAt($key);
        }
    }

    /**
     * Изменяет порядок элементов во всей коллекции на обратный
     */
    public function Reverse()
    {
        $this->exchangeArray(array_reverse($this->ToArray()));
    }

    /**
     * Копирует элементы списка в новый массив
     *
     * @return array
     */
    public function ToArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * Возвращение строки, представляющей текущий объект
     *
     * @return string
     */
    public function ToString()
    {
        return parent::__toString();
    }

}
