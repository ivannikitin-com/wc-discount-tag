<?php
/**
 * Базовый класс триггера
 * Реализует основную логику триггера
 */
namespace WCDT\Triggers;

class AbstractTrigger
{
	/**
	 * ID триггера
 	 */
	public $id = 0;	
	
	/**
	 * Глобальный триггер
 	 */
	public $global = false;
	
	/**
	 * Блокирующий триггер
 	 */
	public $blocking = false;
	
	/**
	 * Значение для проверки
 	 */
	public $value = '';
	
	/**
	 * Название триггера
 	 */
	public $title = '';	
	
	/**
	 * Метод проверяет выполнение триггера
	 * Должен быть перекрыт наследниками
	 * @return bool
	 */
	public function check()
	{
		return false;
	}
}


