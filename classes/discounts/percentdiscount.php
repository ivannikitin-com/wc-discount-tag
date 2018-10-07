<?php
/**
 * Процентная скидка
 * Вычитает или прибавляет процент к цене
 */
namespace WCDT\Discounts;

class PercentDiscount extends AbsctractDiscount
{
	/**
	 * Метод рассчитывает новое значение цены
	 * @param float $prive цена
	 * @return bool
	 */
	public function calculate( $price )
	{
		$result =  $price + $price * $this->value / 100 ;
		
		// Выводим отладочное сообщение 
		WP_DEBUG && $this->isDebugMode && error_log( __CLASS__ . ' корректировка цены цены: ' . $price . ' --> ' . $result );
		
		return $result;
	}	
}