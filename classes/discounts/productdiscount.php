<?php
/**
 * Продуктовая скидка
 * Берет стоимость из продукта
 */
namespace WCDT\Discounts;

class ProductDiscount extends AbsctractDiscount
{
	/**
	 * Метод рассчитывает новое значение цены
	 * @param float $prive цена
	 * @return bool
	 */
	public function calculate( $price )
	{
		return $price;
	}
}