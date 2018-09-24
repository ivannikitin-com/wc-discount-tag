<?php
/**
 * Rласс хранилища в постоянном кэше
 */
namespace WCDT\Storages;

class CacheStorage extends AbstractStorage
{
	/**
	 * Время хранения к кэше - 2 суток
 	 */
	const TRANSIENT = 'wcdt_cache';	
	
	/**
	 * Время хранения к кэше - 2 суток
 	 */
	const EXPIRES = 172800;
	
	/**
	 * Метод востанавливает хранилище
	 * @return mixed
	 */
	protected function restore()
	{
		// Чтение кэша
		$cacheItems = get_transient( self::TRANSIENT );
		
		// Кэш не найден
		if ( $cacheItems === false )
			return array();
		
		// Возврат кэша
		return $cacheItems;
	}
	
	/**
	 * Метод сохраняет хранилище
	 */
	protected function save()
	{
		set_transient( self::TRANSIENT, $this->items, self::EXPIRES );
	}
	
	/**
	 * Метод очищает хранилище
	 */
	public function clear()
	{
		delete_transient( self::TRANSIENT );
		$this->items = array(); 
	}		
}