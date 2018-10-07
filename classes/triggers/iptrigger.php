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
	 * Метод проверяет текущий IP-клиента и возвразает true если он совпадает с заданным 
	 * @return bool
	 */
	public function check()
	{
		$ip = $this->getIP();
		$result = (bool) ( strpos( $this->value, $ip ) !== false );
		
		// Выводим отладочное сообщение 
		WP_DEBUG && $this->isDebugMode &&  error_log( __CLASS__ . 
													 ' результат проверки: ' . var_export( $result, true )  . 
													 ' текущий IP: ' .var_export($ip, true ) . 
													 ' Проверяем: ' . var_export($this->value, true ) );
		
		return $result;
	}
	
	/**  
	 * Метод возвращает текущий IP-клиента
	 * @return string
	 */
	private function getIP()
	{
		// Если включен режим отладки и есть GET-параметр безусловно перезаписываем IP
		if ( $this->isDebugMode && isset( $_GET[ self::GET_PARAM ] ) )
		{
			$debugIP = sanitize_text_field( $_GET[ self::GET_PARAM ] );
			
			// Выводим отладочное сообщение 
			WP_DEBUG && $this->isDebugMode &&  error_log( __CLASS__ . ' переопределение IP: ' . $debugIP );
			
			return $debugIP;
		}
			
		
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