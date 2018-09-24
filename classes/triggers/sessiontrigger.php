<?php
/**
 * Класс реализует сохранение состояния срабатывания в пределах сессиий  
 */
namespace WCDT\Triggers;
use WCDT\Storages\SessionStorage as SessionStorage;

class SessionTrigger extends AbstractTrigger
{
	/**
	 * Сессионное хранилище
 	 */
	protected $session;
	
	/**
	 * Глобальный триггер
 	 */
	protected $key;	
	
	/**
	 * Констуктор класса
 	 */		
    public function __construct() 
	{
		$this->session = SessionStorage::getInstance();
		$this->key = 'trigger_' . $this->id;
	}		
	
	/**
	 * Метод проверяет выполнение триггера
	 * @return bool
	 */
	public function check()
	{
		echo '!!!' .  $this->key;
		// Если в сессии сохранен положительный результат проверки, возвращаем его
		if ( true|| $this->session->getItem( $this->key, 'triggers' ) )
			return true;
		
		// Иначе возвращаем false
		return false;
	}
}