<?php
/**
 * Базовый класс хранилища
 * Реализует основную логику хранения данных
 * Сделан как одиночка, чтобы каждый тип хранилища был в одном экземпляре
 */
namespace WCDT\Storages;

class AbstractStorage
{
	/**
	 * Возвращает экземпляр хранилища
 	 */	
    public static function getInstance()
    {
        static $instances = array();
		
        $calledClass = get_called_class();

        if (!isset($instances[$calledClass]))
        {
            $instances[$calledClass] = new $calledClass();
        }

        return $instances[$calledClass];
    }
	
	/**
	 * Массив объектов хранилища
 	 */
	protected $items;	
	
	/**
	 * Констуктор класса
 	 */		
    protected function __construct() 
	{
		// Инициализация хранилища
		$this->items = $this->restore();
	}
	
	/**
	 * Деструктор класса
 	 */		
	public function __destruct() 
	{
		// Сохранение хранилища
		$this->save();
	}	
	
	/**
	 * Клонирование запрещено
 	 */		
    private function __clone() {}
	
	/**
	 * Метод возвращает объект из хранилища
	 * @param string $key Ключ объекта
	 * @param string $section Раздел хранилища
	 * @return mixed
	 */
	public function getItem( $key, $section = 'default')
	{
		// Проверяем наличие секции
		if ( ! array_key_exists( $section, $this->items ) )
			return null;
		
		// Проверяем наличие секции
		if ( ! array_key_exists( $key, $this->items[ $section ] ) )
			return null;
		
		// Возвращаем ключ
		return $this->items[ $section ][ $key ];
	}
	
	/**
	 * Метод записывает объект в хранилище
	 * @param string $key Ключ объекта
	 * @param string $value Объект
	 * @param string $section Раздел хранилища
	 * @return mixed
	 */
	public function setItem( $key, $value, $section = 'default')
	{
		$this->items[ $section ][ $key ] = $value;
	}
	
	/**
	 * Метод востанавливает хранилище
	 * Должен быть перекрыт потомками, если надо реализовать постоянное хранилище
	 * @return mixed
	 */
	protected function restore()
	{
		return array();
	}
	
	/**
	 * Метод сохраняет хранилище
	 * Должен быть перекрыт потомками, если надо реализовать постоянное хранилище
	 */
	protected function save()
	{
		// Ничего
	}
	
	/**
	 * Метод очищает хранилище
	 * Должен быть перекрыт потомками, если надо реализовать постоянное хранилище
	 */
	public function clear()
	{
		$this->items = array(); 
	}	
}