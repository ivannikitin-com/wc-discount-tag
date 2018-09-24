<?php
/**
 * Класс триггера на IP
 * Реализует проверку по IP адресу
 */
namespace WCDT\Triggers;

class IPTrigger extends AbstractTrigger
{
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

		return $ip;		
	}
}