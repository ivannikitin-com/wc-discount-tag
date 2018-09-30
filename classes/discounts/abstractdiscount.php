<?php
/**
 * Базовый класс скидки
 * Реализует основную логику скидки
 */
namespace WCDT\Discounts;

class AbsctractDiscount
{
	/**
	 * ID скидки
 	 */
	public $id = 0;
	
	/**
	 * Значение скидки
 	 */
	public $value = 0.00;
	
	/**
	 * Метод рассчитывает новое значение цены
	 * Должен быть перекрыт наследниками
	 * @param float $prive цена
	 * @return bool
	 */
	public function calculate( $price )
	{
		return $price;
	}	
}