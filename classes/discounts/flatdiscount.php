<?php
/**
 * Фиксированная скидка
 * Вычитает или прибавляет фиксированную стоимость к цене
 */
namespace WCDT\Discounts;

class FlatDiscount extends AbsctractDiscount
{
	/**
	 * Метод рассчитывает новое значение цены
	 * @param float $prive цена
	 * @return bool
	 */
	public function calculate( $price )
	{
		$result =  $price + $this->value;
		
		// Выводим отладочное сообщение 
		WP_DEBUG && $this->isDebugMode && error_log( __CLASS__ . ' корректировка цены цены: ' . $price . ' --> ' . $result );
		
		return $result;
	}	
}