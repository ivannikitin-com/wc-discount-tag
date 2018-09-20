<?php
/**
 * Интерфейс части настроек
 */
namespace WCDT;

interface ISettingsPart
{
	// Функция возвращает массив с треубемыми настройками
    public function getSettings();
}