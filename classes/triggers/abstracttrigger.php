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
	 * Флаг режима отладки 
	 */
    protected $isDebugMode = false;		
	
	/**
	 * Конструктор 
	 */
	public function __construct()
	{
		// Флаг режима отладки
		$this->isDebugMode = get_option( \WCDT\Plugin::SETTINGS_DEDUG_MODE, false );
		//error_log('AbstractTrigger::__construct выподлнен. $this->isDebugMode ' . var_export( $this->isDebugMode, true) );
	}	
	
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


