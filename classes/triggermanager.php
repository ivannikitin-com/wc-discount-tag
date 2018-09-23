<?php
/**
 * Класс управления триггерами
 * Реализует CPT триггеров
 */
namespace WCDT;
use WCDT\Triggers;

class TriggerManager
{
	/**
	 * CPT триггеров
	 */
	const CPT = 'wcdt_trigger';
	
	/**
	 * Конструктор 
	 */
	public function __construct()
	{
		// Регистрация типа данных
		$this->registerCPT();
		
		if ( is_admin() ) 
		{
			// Инициализация метабокса
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
			
			// Колонки таблицы
			add_filter( 'manage_edit-' . self::CPT . '_columns', array( $this, 'getTableColumns' ) ) ;
			add_action( 'manage_' . self::CPT . '_posts_custom_column', array( $this, 'showTableColumnValues' ), 10, 2 );
		}		
	}
	
	/**
	 * Метод возвращает типы триггеров и реализующие их классы
	 */
	private function getTypes()
	{
		return array(
			'\WCDT\Triggers\IPTrigger' => 'IP адрес',
			'\WCDT\Discounts\QueryStringTrigger' => 'Параметр в URL',
		);
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
	
}