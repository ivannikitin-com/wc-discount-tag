<?php
/**
 * Класс настроект плагина
 * Реализован как Singleton, чтобы любой другой класс мог получить доступ к единственному экземпляру
 */
namespace WCDT;

class Settings
{
	/**
	 * Экземпляр класса
	 * @static  
	 */	
    private static $instance = null;
	
    /**
	 * Возвращает объект параметров
     * @return Settings
     */
    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
	
	/**
	 * Запрещаем клонирование 
	 */
    private function __clone() {}
	
	/**
	 * Зарегистрированнве объекты, которым нужны свои параметры 
	 */
    private $parts = array();	
	
	/**
	 * Идентификатор раздела настроек WC и таба настроек 
	 * @static  
	 */	
    const SECTION_ID = 'wc-discount-tag';	
	
	/**
	 * Закрытый конструктор класса
	 */
    private function __construct() 
	{
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'addSettingsTab'), 50 );				// Добавляет новую страницу в настройки WC
		add_action( 'woocommerce_settings_tabs_'. self::SECTION_ID , array( $this, 'showSettings') );		// Показывает настройки на новой панели
		add_action( 'woocommerce_update_options_'. self::SECTION_ID , array( $this, 'updateSettings') );	// Обновляет настройки на новой панели		
	}
	
	/**
	 * Регистрация объекта, которому нуджны параметры
	 * @param ISettingsPart $part Объект, который реализует интерфейс ISettingsPart
	 */	
    public function registerPart( ISettingsPart $part ) 
	{
		$this->parts[] = $part;
	}	

	/**
	 * Формирует массив настроек
	 * https://docs.woocommerce.com/wc-apidocs/source-class-WC_Admin_Settings.html#189-623
	 */
	private function getSettings()
	{
		// Определение параметров
		$settings = array();
		
		// Начало секции
		$settings[ 'section_title' ] = array(
			'name'     => __( 'Плагин скидочных меток', Plugin::TEXTDOMAIN ),
			'type'     => 'title',
			'desc'     => '',
			'id'       => self::SECTION_ID . '_section_title'
		);
		
		// Параметры зарегистрированных объектов
		foreach ( $this->parts as $part )
		{
			$settings = array_merge( $settings, $part->getSettings() ); 
		}
			
		// Конец секции
		$settings[ 'section_end' ] = array(
			 'type' => 'sectionend',
			 'id' => self::SECTION_ID . '_section_end'
		);
		
		return $settings;
	}	
	
	
	/**
	 * Добавляет новую панель в настройки WooCommerce
	 * @param mixed $tabs Массив панелей WC
	 */
	public function addSettingsTab( $tabs )
	{
		$tabs[ self::SECTION_ID ] = __( 'Скидочные метки', Plugin::TEXTDOMAIN );
		return $tabs;		
	}
	
	/**
	 * Показывает таб настроек
	 */
	public function showSettings()
	{
		woocommerce_admin_fields( $this->getSettings() );		
	}
	
	/**
	 * Обновляет настройки плагина
	 */
	public function updateSettings()
	{
		woocommerce_update_options( $this->getSettings() );		
	}	
	
}