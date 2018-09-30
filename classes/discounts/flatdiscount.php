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
		return $price + $this->value;
	}	
}