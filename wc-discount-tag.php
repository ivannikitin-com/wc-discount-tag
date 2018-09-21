<?php
/**
 * Plugin Name:  WooCommerce Discount Tag
 * Plugin URI:   https://github.com/ivannikitin-com/wc-discount-tag
 * Description:  Плагин скидочных меток для WooCommerce
 * Version:      0.1
 * Author:       Иван Никитин и партнеры
 * Author URI:   https://ivannikitin.com/
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  wc-discount-tag
 * Domain Path:  /lang
 * 
 * WC requires at least: 3.0
 * WC tested up to: 3.4.5
 */
namespace WCDT;

// Запрет на прямой вызов
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Подключение классов
require( 'classes/isettingspart.php' );
require( 'classes/settings.php' );


// Основной класс плагина
class Plugin implements ISettingsPart
{
	/**
	 * Текстоввый домен плагина
	 * @static  
	 */
	const TEXTDOMAIN = 'wc-discount-tag';
	
	/**
	 * Объект параметров 
	 */
    private $settings;		
	
	/**
	 * Конструктор 
	 */
	public function __construct()
	{
		// Если нет WooCommerce, ничего не делаем!
		if (! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
		{
			add_action( 'admin_notices', array( $this, 'showMessageNoWC' ) );
			return;
		}
		
		// Инициализация плагина
		add_action( 'init', array( $this, 'init' ) );
	}
	
	/**
	 * Сообщение об отсуствии WooCommerce 
	 */
	public function showMessageNoWC()
	{
		$class = 'notice notice-error';
		$message = __( 'Для работы плагина WooCommerce Discount Tag требуется установить и активировать WooCommerce!', self::TEXTDOMAIN );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );		
	}
	
	/**
	 * Инициализации плагина 
	 */
	public function init()
	{
		// Объект настроек
		$this->settings = Settings::getInstance();
		$this->settings->registerPart( $this );
	}
	
	/**
	 * Формирует массив настроек
	 */
	public function getSettings()
	{
		return array(
			'debug_mode' => array(
				'name' => __( 'Режим отладки', self::TEXTDOMAIN ),
				'type' => 'checkbox',
				'default' => 0,
				'desc' => __( 'Этот режим включает функции отладки, например, возможность указания любого параметра ip с помощью GET-параметров', INWCOA ),
				'id'   => 'wcdt_debug_mode'
			)			
		);
	}
	
}

// Запуск плагина
new Plugin();