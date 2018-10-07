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
	 * Метод проверяет выполнение триггера
	 * @return bool
	 */
	public function check()
	{
		// Свойства объекта
		$this->key = 'trigger_' . $this->id;
		$this->session = SessionStorage::getInstance();
		
		// Если в сессии сохранен положительный результат проверки, возвращаем его
		if ( $this->session->getItem( $this->key, 'triggers' ) )
		{
			WP_DEBUG && $this->isDebugMode &&  error_log( __CLASS__ . ' в сессии сохранен предудущий результат: true' );
			return true;
		}
			
		
		// Иначе возвращаем false
		return false;
	}
}