<?php
/**
 * Класс хранилища в сессиях пользователя
 */
namespace WCDT\Storages;

class SessionStorage extends AbstractStorage
{
	/**
	 * Ключ для сохранения массива объектов в скссии
 	 */
	const KEY = 'wcdt_session';
	
	/**
	 * Констуктор класса
 	 */		
    protected function __construct() 
	{
		// Старт сессии
		if( empty ( $this->getSessionId() ) )
			session_start();
		
		parent::__construct();
	}
	
	/**
	 * Метод востанавливает хранилище
	 * @return mixed
	 */
	protected function restore()
	{
		//var_dump( $_SESSION );
		
		// Если нет сессии...
		if ( empty( $_SESSION ) )
			return array();
		
		// Если сессиооная переменная не найдена
		if ( ! array_key_exists( self::KEY, $_SESSION ) )
			return array();
		
		// Возврат кэша
		return $_SESSION[ self::KEY ];
	}
	
	/**
	 * Метод сохраняет хранилище
	 */
	protected function save()
	{
		$_SESSION[ self::KEY ] = $this->items;
	}
	
	/**
	 * Метод очищает хранилище
	 */
	public function clear()
	{
		$_SESSION[ self::KEY ] = array();
		$this->items = array(); 
	}
	
	/**
	 * Возвращает ID сессии
	 */
	public function getSessionId()
	{
		return session_id();
	}	
	
}