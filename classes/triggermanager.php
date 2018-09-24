<?php
/**
 * Класс управления триггерами
 * Реализует CPT триггеров
 */
namespace WCDT;
use WCDT\Storages\CacheStorage as CacheStorage;

class TriggerManager
{
	/**
	 * CPT триггеров
	 */
	const CPT = 'wcdt_trigger';
	
	/**
	 * Кэш объектов
 	 */
	public $cache;	
	
	/**
	 * Конструктор 
	 */
	public function __construct()
	{
		// Кэш объектов
		$this->cache = CacheStorage::getInstance();
		
		// Регистрация типа данных
		$this->registerCPT();
		
		// Хуки
		if ( is_admin() ) 
		{
			// Инициализация метабокса
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
			
			// Колонки таблицы
			add_filter( 'manage_edit-' . self::CPT . '_columns', array( $this, 'getTableColumns' ) ) ;
			add_action( 'manage_' . self::CPT . '_posts_custom_column', array( $this, 'showTableColumnValues' ), 10, 2 );
		}
		
		// Начальная проверка всех триггеров зарегистрированных, необходима для сессионных триггеров
		$this->checkTriggers( $this->getTriggers( $this->getAllTriggersIDs() ) );
	}
	
	/** ------------------------------------------------------------------------------------------------------------------------
	 * Регистрация типа  
	 */
	private function registerCPT()
	{
		$labels = array(
			'name'                  => _x( 'Триггеры скидок', 'Post Type General Name', Plugin::TEXTDOMAIN ),
			'singular_name'         => _x( 'Триггер скидок', 'Post Type Singular Name', Plugin::TEXTDOMAIN ),
			'menu_name'             => __( 'Триггеры скидок', Plugin::TEXTDOMAIN ),
			'name_admin_bar'        => __( 'Триггеры', Plugin::TEXTDOMAIN ),
			'archives'              => __( 'Архивы триггеров скидок', Plugin::TEXTDOMAIN ),
			'attributes'            => __( 'Атрибуты триггера', Plugin::TEXTDOMAIN ),
			'parent_item_colon'     => __( 'Родительский триггер:', Plugin::TEXTDOMAIN ),
			'all_items'             => __( 'Триггеры скидок', Plugin::TEXTDOMAIN ),
			'add_new_item'          => __( 'Добавить триггер', Plugin::TEXTDOMAIN ),
			'add_new'               => __( 'Добавить триггер', Plugin::TEXTDOMAIN ),
			'new_item'              => __( 'Новый триггер', Plugin::TEXTDOMAIN ),
			'edit_item'             => __( 'Редактировать', Plugin::TEXTDOMAIN ),
			'update_item'           => __( 'Обновить', Plugin::TEXTDOMAIN ),
			'view_item'             => __( 'Просмотр', Plugin::TEXTDOMAIN ),
			'view_items'            => __( 'Просмотр', Plugin::TEXTDOMAIN ),
			'search_items'          => __( 'Поиск', Plugin::TEXTDOMAIN ),
			'not_found'             => __( 'Триггеры не найдены', Plugin::TEXTDOMAIN ),
			'not_found_in_trash'    => __( 'Триггеры не найдены в корзине', Plugin::TEXTDOMAIN ),
			'featured_image'        => __( 'Изображение', Plugin::TEXTDOMAIN ),
			'set_featured_image'    => __( 'Установить изображение', Plugin::TEXTDOMAIN ),
			'remove_featured_image' => __( 'Удалить изображение', Plugin::TEXTDOMAIN ),
			'use_featured_image'    => __( 'Использовать как изображение', Plugin::TEXTDOMAIN ),
			'insert_into_item'      => __( 'Добавить в элемент', Plugin::TEXTDOMAIN ),
			'uploaded_to_this_item' => __( 'Загрузить для элемента', Plugin::TEXTDOMAIN ),
			'items_list'            => __( 'Список триггеров', Plugin::TEXTDOMAIN ),
			'items_list_navigation' => __( 'Навигация', Plugin::TEXTDOMAIN ),
			'filter_items_list'     => __( 'Фильтр триггеров', Plugin::TEXTDOMAIN ),
		);
		$capabilities = array(
			'edit_post'             => 'manage_woocommerce',
			'read_post'             => 'read_post',
			'delete_post'           => 'manage_woocommerce',
			'delete_posts'          => 'manage_woocommerce',
			'edit_posts'            => 'manage_woocommerce',
			'edit_others_posts'     => 'manage_woocommerce',
			'publish_posts'         => 'manage_woocommerce',
			'read_private_posts'    => 'read_private_posts',
		);
		$args = array(
			'label'                 => __( 'Триггер скидок', Plugin::TEXTDOMAIN ),
			'description'           => __( 'Триггеры скидок', Plugin::TEXTDOMAIN ),
			'labels'                => $labels,
			'supports'              => array( 'title' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => 'edit.php?post_type=product',
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-welcome-view-site',
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => false,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'rewrite'               => false,
			'capabilities'          => $capabilities,
		);
		register_post_type( self::CPT, $args );
	}
	
	/** ------------------------------------------------------------------------------------------------------------------------
	 * Метабокс  
	 */	
	public function init_metabox() 
	{
		add_action( 'add_meta_boxes',        array( $this, 'add_metabox' )         );
		add_action( 'save_post',             array( $this, 'save_metabox' ), 10, 2 );

	}

	public function add_metabox() 
	{
		add_meta_box(
			self::CPT,
			__( 'Свойства', Plugin::TEXTDOMAIN ),
			array( $this, 'renderMetabox' ),
			self::CPT,
			'advanced',
			'high'
		);

	}

	public function renderMetabox( $post ) 
	{
		// Типы триггеров
		$triggerTypes = $this->getTypes();
		$triggerTypeTitles = array_values( $triggerTypes );
		
		// Retrieve an existing value from the database.
		$wcdt_triggertype = get_post_meta( $post->ID, 'wcdt_type', true );
		$wcdt_blocking = get_post_meta( $post->ID, 'wcdt_blocking', true );
		$wcdt_global = get_post_meta( $post->ID, 'wcdt_global', true );
		$wcdt_trigger_value = get_post_meta( $post->ID, 'wcdt_trigger_value', true );

		// Set default values.
		if( empty( $wcdt_triggertype ) ) $wcdt_triggertype = array_keys( $triggerTypes )[ 0 ];
		if( empty( $wcdt_blocking ) ) $wcdt_blocking = '';
		if( empty( $wcdt_global ) ) $wcdt_global = '';
		if( empty( $wcdt_trigger_value ) ) $wcdt_trigger_value = '';

		// Form fields.
		echo '<table class="form-table">';

		echo '	<tr>';
		echo '		<th><label for="wcdt_type" class="wcdt_type_label">' . __( 'Тип триггера', Plugin::TEXTDOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<select id="wcdt_type" name="wcdt_type" class="wcdt_type_field">';
		foreach ( $triggerTypes as $triggerType => $triggerTypeTitle )
		{
			echo '			<option value="' . $triggerType .  '" ' . selected( $wcdt_triggertype, $triggerType, false ) . '> ' .$triggerTypeTitle . '</option>';
		}
		echo '			</select>';
		echo '			<p class="description">' . __( 'Выберите тип триггера', Plugin::TEXTDOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<th><label for="wcdt_blocking" class="wcdt_blocking_label">' . __( 'Блокирующий триггер', Plugin::TEXTDOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<label><input type="checkbox" id="wcdt_blocking" name="wcdt_blocking" class="wcdt_blocking_field" value="checked" ' . checked( $wcdt_blocking, '1', false ) . '></label>';
		echo '			<span class="description">' . __( 'Отметьте, если этот триггер блокирующий', Plugin::TEXTDOMAIN ) . '</span>';
		echo '		</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<th><label for="wcdt_global" class="wcdt_global_label">' . __( 'Глобальный триггер', Plugin::TEXTDOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<label><input type="checkbox" id="wcdt_global" name="wcdt_global" class="wcdt_global_field" value="checked" ' . checked( $wcdt_global, '1', false ) . '></label>';
		echo '			<span class="description">' . __( 'Выберите, если этот триггер глобальный', Plugin::TEXTDOMAIN ) . '</span>';
		echo '		</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<th><label for="wcdt_trigger_value" class="wcdt_trigger_value_label">' . __( 'Значение', Plugin::TEXTDOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcdt_trigger_value" name="wcdt_trigger_value" class="wcdt_trigger_value_field" value="' . esc_attr( $wcdt_trigger_value ) . '">';
		echo '			<p class="description">' . __( 'Укажите значение для активации триггера', Plugin::TEXTDOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';

		echo '</table>';

	}

	public function save_metabox( $post_id, $post ) 
	{
		// Sanitize user input.
		$wcdt_new_type = isset( $_POST[ 'wcdt_type' ] ) ? $_POST[ 'wcdt_type' ] : '';
		$wcdt_new_blocking = isset( $_POST[ 'wcdt_blocking' ] ) ? '1'  : '';
		$wcdt_new_global = isset( $_POST[ 'wcdt_global' ] ) ? '1'  : '';
		$wcdt_new_value = isset( $_POST[ 'wcdt_trigger_value' ] ) ? sanitize_text_field( $_POST[ 'wcdt_trigger_value' ] ) : '';

		// Update the meta field in the database.
		update_post_meta( $post_id, 'wcdt_type', $wcdt_new_type );
		update_post_meta( $post_id, 'wcdt_blocking', $wcdt_new_blocking );
		update_post_meta( $post_id, 'wcdt_global', $wcdt_new_global );
		update_post_meta( $post_id, 'wcdt_trigger_value', $wcdt_new_value );
		
		// Очистка кэша
		$this->cache->clear();
	}
	
	/** ------------------------------------------------------------------------------------------------------------------------
	 * Колонки таблицы  
	 */	
	public function getTableColumns( $columns ) 
	{
		return array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Триггер', Plugin::TEXTDOMAIN ),
			'type' => __( 'Тип', Plugin::TEXTDOMAIN ),
			'value' => __( 'Значение', Plugin::TEXTDOMAIN ),
			'global' => __( 'Глобальный', Plugin::TEXTDOMAIN ),
			'blocking' => __( 'Блокирующий', Plugin::TEXTDOMAIN ),
		);
	}
	
	public function showTableColumnValues( $column, $post_id ) 
	{
		switch( $column )
		{
			case 'type':
				$wcdt_triggertype = get_post_meta( $post_id, 'wcdt_type', true );
				echo $this->getTypes()[ $wcdt_triggertype ];
				break;

			case 'value':
				echo get_post_meta( $post_id, 'wcdt_trigger_value', true );
				break;
				
			case 'global':
				if ( get_post_meta( $post_id, 'wcdt_global', true ) )
				{
					echo __( 'Да', Plugin::TEXTDOMAIN );
				}
				break;
				
			case 'blocking':
				if ( get_post_meta( $post_id, 'wcdt_blocking', true ) )
				{
					echo __( 'Да', Plugin::TEXTDOMAIN );
				}
				break;
		}
	}
	
	/** ------------------------------------------------------------------------------------------------------------------------
	 * Логика триггеров
	 */
	
	/**
	 * Метод возвращает типы триггеров и реализующие их классы
	 */
	private function getTypes()
	{
		return array(
			'\WCDT\Triggers\IPTrigger' => 'IP адрес',
			'\WCDT\Triggers\QueryStringTrigger' => 'Параметр в URL',
		);
	}
	
	/**
	 * Создает экзмепляр триггера
	 * @param int $id ID триггера
	 * @return AbstractTrigger
	 */
	private function createTrigger( $id )
	{
		// Тип класса триггера
		$class = get_post_meta( $id, 'wcdt_type', true );
		
		// Пытаемся создать триггер
		try 
		{
			$trigger = new $class();
		} 
		catch (\Exception $e) 
		{
			throw new UndefinedTriggerException( __( 'Триггер неопределен.' . 'ID: '. $id . ' Class: ' . $class ) );
		}
		
		$trigger->id = $id;
		$trigger->global = get_post_meta( $id, 'wcdt_blocking', true );
		$trigger->blocking = get_post_meta( $id, 'wcdt_global', true );
		$trigger->value = get_post_meta( $id, 'wcdt_trigger_value', true );
		$trigger->title = get_the_title( $id );
		
		return $trigger;
	}	
	
	/**
	 * Реализует основную логику проверки
	 *		Получает список ID триггеров в виде массива или строку с ID разделенные запятыми
	 *		Проверяет глобальные триггеры, 
	 *			Если сработал хоть один блокирующий, возвращает false
	 *			Если сработал хоть один обычный, возвращает true
	 *   	Формирует список переданных триггеров
	 *			Если сработал хоть один блокирующий, возвращает false
	 *			Если сработал хоть один обычный, возвращает true
	 *   
	 * @param mixed | string $ids массив или строка с ID триггеров
	 * @return bool
	 */
	public function check( $ids )
	{
		// Списки глобальгых триггеров
		$globalTriggers = $this->getTriggers( $this->getGlobalTriggersIDs() );
		$glovalBlockingTriggers = array();
		$glovalRegularTriggers = array();
		foreach ( $globalTriggers as $trigger )
		{
			if ( $trigger->blocking )
				$glovalBlockingTriggers[] = $trigger;
			else
				$glovalRegularTriggers[] = $trigger;
		}
		
		// Если сработал хоть один блокирующий глобальный триггер, возвращаем false
		if ( $this->checkTriggers( $glovalBlockingTriggers, true ) )
			return false;
		
		// Если сработал хоть один обычный глобальных триггер, возвращаем true
		if ( $this->checkTriggers( $glovalRegularTriggers, true ) )
			return true;		
		
		// Списки обычных триггеров
		$triggers = $this->getTriggers( $ids );
		$blockingTriggers = array();
		$regularTriggers = array();
		foreach ( $triggers as $trigger )
		{
			if ( $trigger->blocking )
				$blockingTriggers[] = $trigger;
			else
				$regularTriggers[] = $trigger;
		}
		
		// Если сработал хоть один блокирующий триггер, возвращаем false
		if ( $this->checkTriggers( $blockingTriggers, true ) )
			return false;
		
		// Возвращаем результат проверки обычных триггеров
		return $this->checkTriggers( $regularTriggers, true );
	}
	
	/**
	 * Проверяет старбатывание триггеров в массиве триггеров и возвращает результат
	 * @param mixed $triggers массив триггеров
	 * @param bool $lazyCheck режим ленивой проверки. Возврашает true при первом срабатывании и далее проверка не идет
	 * @return bool
	 */
	private function checkTriggers( $triggers, $lazyCheck = false)
	{
		$result = false;
		foreach( $triggers as $trigger )
		{
			$triggerResult = $trigger->check();
			if ( $triggerResult && $lazyCheck)
				return true;
			$result = $result || $triggerResult;
		}
		return $result;
	}	
	
	/**
	 * Создает массив триггеров по массиву id или строке, содержащей список ID через запятую 
	 * @param mixed | string $ids массив или строка с ID триггеров
	 * @return mixed AbstractTrigger
	 */
	public function getTriggers( $ids )
	{
		// Ключ для сохранения к кэше
		$keyName = 'IDs:';
		
		// Подготовка ключа и массива IDs
		if ( gettype( $ids ) == 'string' )
		{
			$keyName .= $ids;
			$ids = explode(',', $ids );
		}
		else
		{
			$keyName .= implode(',', $ids);
		}
		
		// Проверяем наличие в кэше
		$triggers = $this->cache->getItem( $keyName, 'triggers' );
		if ( ! empty( $triggers ) )
			return $triggers;
		
		// Формируем массив триггеров
		$triggers = array();
		foreach ( $ids as $id )
		{
			$triggers[] = $this->createTrigger( $id );
		}
		
		// Сохраняем к кэш и возвращаем результат
		$this->cache->setItem( $keyName, $triggers, 'triggers' );
		return $triggers; 		
	}

	/**
	 * Метод возвращает все возможные id триггеров из БД
	 * @return mixed int
	 */
	private function getAllTriggersIDs()
	{
		// Проверяем наличие в кэше
		$ids = $this->cache->getItem( 'all IDs', 'triggers' );
		if ( ! empty( $ids ) )
			return $ids;
			
		$args = array(
			'post_type'		=> array( self::CPT ),
			'post_status'	=> array( 'publish' ),
			'fields'		=> 'ids',
		);
		
		// The Query
		$query = new \WP_Query;
		$ids = $query->query( $args );
		
		// Сохраняем к кэш и возвращаем результат
		$this->cache->setItem( 'all IDs', $ids, 'triggers' );
		return $ids; 
	}
	
	/**
	 * Метод возвращает id глобальных триггеров из БД
	 * @return mixed int
	 */
	public function getGlobalTriggersIDs()
	{
		// Проверяем наличие в кэше
		$ids = $this->cache->getItem( 'global IDs', 'triggers' );
		if ( ! empty( $ids ) )
			return $ids;
			
		$args = array(
			'post_type'              => array( self::CPT ),
			'post_status'            => array( 'publish' ),
			'fields'				 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'wcdt_global',
					'value'   => '1',
				),
			),
		);
		
		// The Query
		$query = new \WP_Query;
		$ids = $query->query( $args );
		
		// Сохраняем к кэш и возвращаем результат
		$this->cache->setItem( 'global IDs', $ids, 'triggers' );
		return $ids; 
	}
	
	/**
	 * Метод возвращает id неглобальных триггеров из БД
	 * @return mixed int
	 */
	public function getNonGlobalTriggersIDs()
	{
		// Проверяем наличие в кэше
		$ids = $this->cache->getItem( 'non-global IDs', 'triggers' );
		if ( ! empty( $ids ) )
			return $ids;
			
		$args = array(
			'post_type'              => array( self::CPT ),
			'post_status'            => array( 'publish' ),
			'fields'				 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'wcdt_global',
					'value'   => '',
				),
			),
		);
		
		// The Query
		$query = new \WP_Query;
		$ids = $query->query( $args );
		
		// Сохраняем к кэш и возвращаем результат
		$this->cache->setItem( 'non-global IDs', $ids, 'triggers' );
		return $ids; 
	}	
}