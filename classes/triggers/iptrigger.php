<?php
/**
 * Класс триггера на IP
 * Реализует проверку по IP адресу
 */
namespace WCDT\Triggers;

class IPTrigger extends AbstractTrigger
{
	/**
	 * GET-параметр, который позволяет задать IP в режиме отладки
	 * @static  
	 */
	const GET_PARAM = 'ip';	
	
	/**
	 * Флаг режима отладки 
	 */
    private $isDebugMode = false;		
	
	/**
	 * Конструктор 
	 */
	public function __construct()
	{
		// Флаг режима отладки
		$this->isDebugMode = get_option( \WCDT\Plugin::SETTINGS_DEDUG_MODE, false );
	}
	
	/**
	 * Метод проверяет текущий IP-клиента и возвразает true если он совпадает с заданным 
	 * @return bool
	 */
	public function check()
	{
		return ( strpos( $this->value, $this->getIP() ) !== false );
	}
	
	/**  
	 * Метод возвращает текущий IP-клиента
	 * @return string
	 */
	private function getIP()
	{
		// Если включен режим отладки и есть GET-параметр безусловно перезаписываем IP
		if ( $this->isDebugMode && isset( $_GET[ self::GET_PARAM ] ) )
			return sanitize_text_field( $_GET[ self::GET_PARAM ] );
		
		$ip = '';
		
		if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) )
		{
			//check for ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
		{
			// Check for the Proxy User
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return sanitize_text_field( $ip );	
	}
}