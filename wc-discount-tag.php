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
require_once( 'classes/isettingspart.php' );
require_once( 'classes/settings.php' );
require_once( 'classes/storages/abstractstorage.php' );
require_once( 'classes/storages/cachestorage.php' );
require_once( 'classes/storages/sessionstorage.php' );
require_once( 'classes/discountmanager.php' );
require_once( 'classes/discounts/abstractdiscount.php' );
require_once( 'classes/triggermanager.php' );
require_once( 'classes/triggers/exceptions.php' );
require_once( 'classes/triggers/abstracttrigger.php' );
require_once( 'classes/triggers/iptrigger.php' );
require_once( 'classes/triggers/sessiontrigger.php' );
require_once( 'classes/triggers/querystringtrigger.php' );


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
	 * Объект менеджера скидок 
	 */
    private $discountManager;	
	
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

		// Инициализация админской части
		if ( is_admin() ) 
		{
			add_action( 'admin_enqueue_scripts', array( $this, 'loadAdminScripts' ) );
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
		
		// Объект менеджера скидок
		$this->discountManager = new DiscountManager();
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
	
	/** 
	 * Загрузка сервисных скриптов и стилей 
	 */	
	function loadAdminScripts()
	{
		// Chosen Select jquery
		// https://wordpress.stackexchange.com/questions/217691/chosen-select-jquery-not-working-in-plugin
		// https://cdnjs.com/libraries/chosen
		$choosenVer = '1.8.7';
		wp_register_style( 'chosen-css', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css', array(), $choosenVer, 'all' );
		wp_register_script( 'chosen-js', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js', array( 'jquery' ), $choosenVer, true );
		wp_enqueue_style( 'chosen-css' );
		wp_enqueue_script( 'chosen-js' );		
	}
	
}

// Запуск плагина
new Plugin();