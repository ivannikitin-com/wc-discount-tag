<?php
/**
 * Класс триггера на IP
 * Реализует проверку по IP адресу
 */
namespace WCDT\Triggers;

class QueryStringTrigger extends SessionTrigger
{
	/**
	 * Метод проверяет выполнение триггера
	 * @return bool
	 */
	public function check()
	{
		// Если в сессии сохранен положительный результат проверки, возвращаем его
		if ( parent::check() )
			return true;
		
		// Если нечего проверять, возвращаем false
		$queryString = sanitize_text_field( $_SERVER[ 'QUERY_STRING' ] );
		if ( empty( $queryString ) )
			return false;
		
		// Если есть указанное значение в строке запроса
		if ( strpos( $queryString, $this->value ) !== false )
		{
			WP_DEBUG && $this->isDebugMode &&  error_log( __CLASS__ . ' результат проверки: true' );
			
			// Сохраняем в сессии проверку
			$this->session->setItem( $this->key, true, 'triggers' );
			return true;
		}
		
		return false;
	}
}