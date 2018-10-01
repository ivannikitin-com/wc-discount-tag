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
	 * Значение скидки
 	 */
	private $triggers = array();	
	
	/**
	 * Конструктор 
	 * @param int $id	ID скидки
	 * @param float $value	Размер скидки
	 * @param mixed AbstractTrigger $triggers массив триггеров
	 */
	public function __construct( $id, $value, $triggers )
	{
		$this->id = $id;
		$this->value = $value;
		$this->triggers = $triggers;
	}
	
	/**
	 * Метод возвращает триггеры скидки
	 * @return mixed AbstractTrigger
	 */
	public function getTriggers()
	{
		return $this->triggers;
	}
	
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