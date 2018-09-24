<?php
/**
 * Класс триггера на IP
 * Реализует проверку по IP адресу
 */
namespace WCDT\Triggers;

class QueryStringTrigger extends SessionTrigger
{
	/**
	 * Констуктор класса
 	 */		
    public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Метод проверяет выполнение триггера
	 * @return bool
	 */
	public function check()
	{
		// Если в сессии сохранен положительный результат проверки, возвращаем его
		if ( parent::check() )
			return true;
		
		// Если есть указанное значение в строке запроса
		if ( strpos( $this->value, $_SERVER[ 'QUERY_STRING' ] ) !== false )
		{
			// Сохраняем в сессии проверку
			$this->session->setItem( $this->key, true, 'triggers' );
			return true;
		}
		
		return false;
	}
}