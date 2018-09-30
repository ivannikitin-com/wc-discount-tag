<?php
/**
 * Класс управления скидками
 * Реализует таксономию скидок
 */
namespace WCDT;
use WCDT\Storages\SessionStorage as SessionStorage;

class DiscountManager
{
	/**
	 * Таксономия скидок
	 */
	const TAXONOMY = 'wcdt_discount';
	
	/**
	 * Объект менеджера триггеров 
	 */
    private $triggerManager;		
	
	/**
	 * Конструктор 
	 */
	public function __construct()
	{
		// Инициализация менеджера триггеров
		$this->triggerManager = new TriggerManager();
		
		// Регистрация таксономии
		$this->registerTaxonomy();
		
		// Проыверка админки
		if ( is_admin() ) 
		{
			// Хуки админки
			add_action( self::TAXONOMY . '_add_form_fields',  array( $this, 'create_screen_fields'), 10, 1 );
			add_action( self::TAXONOMY . '_edit_form_fields', array( $this, 'edit_screen_fields' ),  10, 2 );

			add_action( 'created_' . self::TAXONOMY, array( $this, 'save_data' ), 10, 1 );
			add_action( 'edited_' . self::TAXONOMY,  array( $this, 'save_data' ), 10, 1 );
			
			// Таблица скидок
			add_filter( 'manage_edit-' . self::TAXONOMY . '_columns', array( $this, 'getTableColumns' ) );
			add_filter( 'manage_' . self::TAXONOMY . '_custom_column', array( $this, 'getTableColumnContent' ), 10, 3 );
			add_filter( 'manage_edit-' . self::TAXONOMY . '_sortable_columns', array( $this, 'getTableSortableColumns' ) );
		}
		else
		{
			// Хуки цены, ставим ТОЛЬКО на фронтэнде!
			
			
			// https://stackoverflow.com/questions/45806249/change-product-prices-via-a-hook-in-woocommerce-3
			// Simple, grouped and external products
			add_filter( 'woocommerce_product_get_price', array( $this, 'getProductPrice' ), 99, 2 );
			add_filter( 'woocommerce_product_get_regular_price', array( $this, 'getProductPrice' ), 99, 2 );
			
			// Variations
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'getProductPrice' ), 99, 2 );				
			add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'getProductPrice' ), 99, 2 );
			
			// Variable (price range)
			add_filter( 'woocommerce_variation_prices_price', array( $this, 'getProductVariablePrice' ), 99, 3 );			
			add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'getProductVariablePrice' ), 99, 3 );
			
			// Caching and dynamic pricing – upcoming changes to the get_variation_prices method
			add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'getVariationPricesHash' ), 99, 1 );
		}
		

	}

	/** ------------------------------------------------------------------------------------------------------------------------
	 * Классы и реализация скидок 
	 */	
	
	/**
	 * Метод возвращает типы скидок и реализующие их классы
	 */
	private function getTypes()
	{
		return array(
			'\WCDT\Discounts\FlatDiscount' => 'Фиксированная скидка',
			'\WCDT\Discounts\PercentDiscount' => 'Процентовая скидка',
			'\WCDT\Discounts\ProductDiscount' => 'Цена со скидкой указана в продукте',
		);
	}
	
	/**
	 * Создает экзмепляр триггера
	 * @param int $id ID триггера
	 * @return AbsctractDiscount
	 */
	private function createDiscount( $id )
	{
		// Тип класса триггера
		$class = get_term_meta( $id, 'wcdt_discounttype', true );
		
		// Пытаемся создать объект скидки
		try 
		{
			$discount = new $class();
		} 
		catch (\Exception $e) 
		{
			throw new UndefinedDiscountException( __( 'Скидка не неопределена.' . 'ID: '. $id . ' Class: ' . $class ) );
		}
		
		$discount->id = $id;
		$discount->value = get_term_meta( $id, 'wcdt_discountvalue', true );
		
		return $discount;
	}
	
	/**
	 * Создает массив скидок по массиву id или строке, содержащей список ID через запятую 
	 * @param mixed | string $ids массив или строка с ID скидок
	 * @return mixed AbsctractDiscount
	 */
	public function getDiscounts( $ids )
	{
		// Если передана строка. формируем массив ID скидок
		if ( gettype( $ids ) == 'string' )
		{
			$ids = explode(',', $ids );
		}
		
		// Формируем массив триггеров
		$discounts = array();
		foreach ( $ids as $id )
		{
			$discounts[] = $this->createDiscount( $id );
		}
		
		return $discounts;
	}
	
	/**
	 * Возвращает массив скидок для указанного продукта 
	 * @param int $productId ID продукта
	 * @return mixed AbsctractDiscount
	 */
	public function getProductDiscounts( $productId )
	{
		// ID скидок для этого продукта
		$discountIds = wp_get_post_terms( $productId, self::TAXONOMY, array( 'fields' => 'ids' ) );
		return $this->getDiscounts( $discountIds );
	}
	
	/** ------------------------------------------------------------------------------------------------------------------------
	 * Регистрация таксономии 
	 */
	private function registerTaxonomy()
	{
		$labels = array(
			'name'                       => _x( 'Скидки', 'Taxonomy General Name', Plugin::TEXTDOMAIN ),
			'singular_name'              => _x( 'Скидка', 'Taxonomy Singular Name', Plugin::TEXTDOMAIN ),
			'menu_name'                  => __( 'Скидки', Plugin::TEXTDOMAIN ),
			'all_items'                  => __( 'Все скидки', Plugin::TEXTDOMAIN ),
			'parent_item'                => __( 'Родительский элемент', Plugin::TEXTDOMAIN ),
			'parent_item_colon'          => __( 'Родительский элемент:', Plugin::TEXTDOMAIN ),
			'new_item_name'              => __( 'Новая скидка', Plugin::TEXTDOMAIN ),
			'add_new_item'               => __( 'Добавить скидку', Plugin::TEXTDOMAIN ),
			'edit_item'                  => __( 'Редактировать', Plugin::TEXTDOMAIN ),
			'update_item'                => __( 'Обновить', Plugin::TEXTDOMAIN ),
			'view_item'                  => __( 'Просмотр', Plugin::TEXTDOMAIN ),
			'separate_items_with_commas' => __( 'Скидки через запятую', Plugin::TEXTDOMAIN ),
			'add_or_remove_items'        => __( 'Добавить или удалить скидки', Plugin::TEXTDOMAIN ),
			'choose_from_most_used'      => __( 'Часто используемые скидки', Plugin::TEXTDOMAIN ),
			'popular_items'              => __( 'Популярная скидки', Plugin::TEXTDOMAIN ),
			'search_items'               => __( 'Поиск скидок', Plugin::TEXTDOMAIN ),
			'not_found'                  => __( 'Скидки не найдены', Plugin::TEXTDOMAIN ),
			'no_terms'                   => __( 'Нет скидок', Plugin::TEXTDOMAIN ),
			'items_list'                 => __( 'Список скидок', Plugin::TEXTDOMAIN ),
			'items_list_navigation'      => __( 'Навигация по скидкам', Plugin::TEXTDOMAIN ),
		);
		
		$capabilities = array(
			'manage_terms'               => 'manage_woocommerce',
			'edit_terms'                 => 'manage_woocommerce',
			'delete_terms'               => 'manage_woocommerce',
			'assign_terms'               => 'manage_woocommerce',
		);
		
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
			'rewrite'                    => false,
			'capabilities'               => $capabilities,
			'update_count_callback'      => '_update_post_term_count',
			'show_in_rest'               => false,
		);
		
		register_taxonomy( 'wcdt_discount', array( 'product' ), $args );		
	}
	
	public function create_screen_fields( $taxonomy ) 
	{

		// Типы скидок
		$discountTypes = $this->getTypes();
		$discountTypeTitles = array_values( $discountTypes );
		
		// Список неглобальных триггеров
		$triggers = $this->triggerManager->getTriggers(  $this->triggerManager->getNonGlobalTriggersIDs() );		
		
		
		// Set default values.
		$wcdt_discounttype = array_keys( $discountTypes )[ 0 ];
		$wcdt_discountvalue = '0';
		$wcdt_triggers = '';

		// Form fields.
		echo '<div class="form-field term-wcdt_discounttype-wrap">';
		echo '	<label for="wcdt_discounttype">' . __( 'Тип скидки', Plugin::TEXTDOMAIN ) . '</label>';
		echo '	<select id="wcdt_discounttype" name="wcdt_discounttype">';
		foreach ( $discountTypes as $discountType => $discountTypeTitle )
		{
			echo '		<option value="' . $discountType . '" ' . selected( $wcdt_discounttype, $discountType, false ) . '> ' . $discountTypeTitle . '</option>';
		}

		echo '	</select>';
		echo '	<p class="description">' . __( 'Выберите тип скидки', Plugin::TEXTDOMAIN ) . '</p>';
		echo '</div>';

		echo '<div class="form-field term-wcdt_discountvalue-wrap">';
		echo '	<label for="wcdt_discountvalue">' . __( 'Значение', Plugin::TEXTDOMAIN ) . '</label>';
		echo '	<input type="text" id="wcdt_discountvalue" name="wcdt_discountvalue" placeholder="' . esc_attr__( '', Plugin::TEXTDOMAIN ) . '" value="' . esc_attr( $wcdt_discountvalue ) . '">';
		echo '	<p class="description">' . __( 'Укажите значение скидки', Plugin::TEXTDOMAIN ) . '</p>';
		echo '</div>';
		
		// Choosen select: https://harvesthq.github.io/chosen/
		echo '<div class="form-field term-wcdt_triggers-wrap"><script>jQuery(function($){ $("#wcdt_triggers").chosen({width: "95%"}); })</script>';
		echo '	<label for="wcdt_triggers">' . __( 'Триггеры', Plugin::TEXTDOMAIN ) . '</label>';
		echo '	<select id="wcdt_triggers" name="wcdt_triggers[]" multiple data-placeholder="' .  __( 'Выберите триггеры активации', Plugin::TEXTDOMAIN ) .  '">';
		foreach ( $triggers as $trigger )
		{
			echo '		<option value="' . $trigger->id . '"' . $this->selected( $wcdt_triggers, $trigger->id ) . '> ' . $trigger->title  . '</option>';
		}		
		echo '	</select>';
		echo '</div>';

	}

	public function edit_screen_fields( $term, $taxonomy ) 
	{
		// Типы скидок
		$discountTypes = $this->getTypes();
		$discountTypeTitles = array_values( $discountTypes );		
		
		// Retrieve an existing value from the database.
		$wcdt_discounttype = get_term_meta( $term->term_id, 'wcdt_discounttype', true );
		$wcdt_discountvalue = get_term_meta( $term->term_id, 'wcdt_discountvalue', true );
		$wcdt_triggers = get_term_meta( $term->term_id, 'wcdt_triggers', true );

		// Set default values.
		if( empty( $wcdt_discounttype ) ) $wcdt_discounttype = '';
		if( empty( $wcdt_discountvalue ) ) $wcdt_discountvalue = '';
		if( empty( $wcdt_triggers ) ) $wcdt_triggers = '';
		
		// Список неглобальных триггеров
		$triggers = $this->triggerManager->getTriggers(  $this->triggerManager->getNonGlobalTriggersIDs() );

		// Form fields.
		echo '<tr class="form-field term-wcdt_discounttype-wrap">';
		echo '<th scope="row">';
		echo '	<label for="wcdt_discounttype">' . __( 'Тип скидки', Plugin::TEXTDOMAIN ) . '</label>';
		echo '</th>';
		echo '<td>';
		echo '	<select id="wcdt_discounttype" name="wcdt_discounttype">';
		foreach ( $discountTypes as $discountType => $discountTypeTitle )
		{		
			echo '		<option value="' . $discountType . '" ' . selected( $wcdt_discounttype, $discountType, false ) . '> ' . $discountTypeTitle . '</option>';
		}
		echo '	</select>';
		echo '	<p class="description">' . __( 'Выберите тип скидки', Plugin::TEXTDOMAIN ) . '</p>';
		echo '</td>';
		echo '</tr>';

		echo '<tr class="form-field term-wcdt_discountvalue-wrap">';
		echo '<th scope="row">';
		echo '	<label for="wcdt_discountvalue">' . __( 'Значение', Plugin::TEXTDOMAIN ) . '</label>';
		echo '</th>';
		echo '<td>';
		echo '	<input type="text" id="wcdt_discountvalue" name="wcdt_discountvalue" placeholder="" value="' . esc_attr( $wcdt_discountvalue ) . '">';
		echo '	<p class="description">' . __( 'Укажите значение скидки', Plugin::TEXTDOMAIN ) . '</p>';
		echo '</td>';
		echo '</tr>';
		
		// Form fields.
		echo '<tr class="form-field term-wcdt_triggers-wrap">';
		echo '<th scope="row">';
		echo '	<label for="wcdt_triggers">' . __( 'Триггеры', Plugin::TEXTDOMAIN ) . '</label>';
		echo '</th>';
		echo '<td><script>jQuery(function($){ $("#wcdt_triggers").chosen({width: "95%"}); })</script>';
		echo '	<select id="wcdt_triggers" name="wcdt_triggers[]" multiple data-placeholder="' .  __( 'Выберите триггеры активации', Plugin::TEXTDOMAIN ) .  '">';
		foreach ( $triggers as $trigger )
		{
			echo '		<option value="' . $trigger->id . '"' . $this->selected( $wcdt_triggers, $trigger->id ) . '> ' . $trigger->title  . '</option>';
		}
		echo '	</select>';
		echo '</td>';
		echo '</tr>';

	}
	
	/**
	 * Сервисный метод выводит selected если второе занчение является подстрокой первого
	 * @param string | mixed $values Исходная строка со значениями
	 * @param string $find Значение, которое ищется
	 * @return string
	 */
	private function selected( $values, $find )
	{
		if ( gettype( $values ) == 'array' )
		{
			if ( count($values ) == 0 )
				return '';
			
			$values = implode( ' ', $values );
		}
			
		
		if ( strpos( trim( $values ), trim( $find ) ) !== false )
			return ' selected';
		else
			return '';
	}

	public function save_data( $term_id ) 
	{
		// Sanitize user input.
		$wcdt_new_discounttype = isset( $_POST[ 'wcdt_discounttype' ] ) ? $_POST[ 'wcdt_discounttype' ] : '';
		$wcdt_new_discountvalue = isset( $_POST[ 'wcdt_discountvalue' ] ) ? sanitize_text_field( $_POST[ 'wcdt_discountvalue' ] ) : '';
		$wcdt_new_triggers = isset( $_POST[ 'wcdt_triggers' ] ) ? $_POST[ 'wcdt_triggers' ] : array();

		// Update the meta field in the database.
		update_term_meta( $term_id, 'wcdt_discounttype', $wcdt_new_discounttype );
		update_term_meta( $term_id, 'wcdt_discountvalue', $wcdt_new_discountvalue );
		update_term_meta( $term_id, 'wcdt_triggers', $wcdt_new_triggers );

	}
	
	/**
	 * Метод возвращает колонки в таблице скидок
	 */
	public function getTableColumns( $columns )
	{
		/*
		$columns: array(5) {
		  ["cb"]=> string(25) "<input type="checkbox" />"
		  ["name"]=> string(16) "Название"
		  ["description"]=> string(16) "Описание"
		  ["slug"]=> string(10) "Ярлык"
		  ["posts"]=> string(12) "Записи"
		}		
		*/
		
		$newColumns = array();
		$newColumns['cb'] = $columns['cb'];
		$newColumns['name'] = $columns['name'];
		$newColumns['description'] = $columns['description'];
		$newColumns['wcdt_discounttype'] = __( 'Тип скидки', Plugin::TEXTDOMAIN );		
		$newColumns['wcdt_discountvalue'] = __( 'Величина скидки', Plugin::TEXTDOMAIN );
		$newColumns['wcdt_triggers'] = __( 'Триггеры', Plugin::TEXTDOMAIN );
		$newColumns['posts'] = __( 'Продукты', Plugin::TEXTDOMAIN );
		
		return $newColumns;
	}
	
	/**
	 * Метод возвращает значения колонок в таблице скидок
	 */
	public function getTableColumnContent( $content, $column_name, $term_id )
	{
		// Типы скидок
		$discountTypes = $this->getTypes();

		switch ( $column_name )
		{
			case 'wcdt_discounttype':
				return $discountTypes[ get_term_meta( $term_id, 'wcdt_discounttype', true ) ];
				
			case 'wcdt_discountvalue':
				return get_term_meta( $term_id, 'wcdt_discountvalue', true );
				
			case 'wcdt_triggers':
				$triggers = $this->triggerManager->getTriggers( get_term_meta( $term_id, 'wcdt_triggers', true ) );
				$triggerString = '';
				foreach( $triggers as $trigger )
				{
					$triggerString .= $trigger->title . '<br>';
				}
				$triggerString .= '';
				return $triggerString;
			
			default:
				return $content;
		}
	}
	
	/**
	 * Метод возвращает список колонок, по которым возможна сортировка
	 */
	public function getTableSortableColumns( $sortable )
	{
		$sortable[ 'wcdt_discounttype' ] = 'wcdt_discounttype';
		$sortable[ 'posts' ] = 'posts';
		return $sortable;			
	}
	
	/** ------------------------------------------------------------------------------------------------------------------------
	 * Обработчики скидок WooCommerce
	 */
	
	/**
	 * Обработчик хуков цены
	 * https://stackoverflow.com/questions/45806249/change-product-prices-via-a-hook-in-woocommerce-3
	 *
	 * @param float $price Переданная цена
	 * @param WC_Product $product	Продукт
	 * @return float
	 */
	public function getProductPrice( $price, $product )
	{
		return $this->calculatePrice( $price, $product->get_id() );
	}	
	
	/**
	 * Обработчик хуков диапазона цены
	 * https://stackoverflow.com/questions/45806249/change-product-prices-via-a-hook-in-woocommerce-3
	 *
	 * @param float $price Переданная цена
	 * @param 	 $variation	
	 * @param WC_Product $product	Продукт
	 * @return float
	 */
	public function getProductVariablePrice( $price, $variation, $product )
	{
		return $this->calculatePrice( $price, $product->get_id() ); 		
	}
	
	/**
	 * Caching and dynamic pricing
	 * https://woocommerce.wordpress.com/2015/09/14/caching-and-dynamic-pricing-upcoming-changes-to-the-get_variation_prices-method/
	 *
	 * @param mixed $hash
	 * @return float
	 */	
	public function getVariationPricesHash( $hash )
	{
		$session = SessionStorage::getInstance();
		$hash[] = $session->getSessionId();
		return $hash;
	}
	
	/** ------------------------------------------------------------------------------------------------------------------------
	 * Логика скидок
	 */	
	
	/**
	 * Обработка цены
	 * @param float $price	Переданная цена
	 * @param int	$productId ID продукта	
	 * @return float
	 */
	public function calculatePrice( $price, $productId )
	{
		$discounts = $this->getProductDiscounts( $productId );
		
		// Обработаем каждую скидку
		foreach ( $discounts as $discount )
		{
			// Обработка цены
			$price = $discount->calculate( $price );
		}
		
		return $price;
	}	
	
}