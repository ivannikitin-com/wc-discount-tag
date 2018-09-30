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
		return $price + $price * $this->value / 100 ;
	}	
}