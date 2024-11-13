<?php
class wstr_post_types
{
    // Constructor to initialize the custom post type.
    public function __construct()
    {
        // Hook into the 'init' action to register the custom post type.
        add_action('init', array($this, 'register_domain_post_type'));
        add_action('init', array($this, 'register_domain_taxonomies'));
        add_action('init',  array($this, 'create_domain_order_post_type'));
        add_action('init', array($this, 'create_faq_post_type'));
        add_action('init',  array($this, 'create_faq_cat_taxonomy'));

        // Hook into the 'domain_industry_add_form_fields' and 'domain_industry_edit_form_fields' actions to add image field.
        add_action('domain_industry_add_form_fields', array($this, 'add_taxonomy_image_field'));
        add_action('domain_industry_edit_form_fields', array($this, 'edit_taxonomy_image_field'));

        // Hook into the 'created_domain_industry' and 'edited_domain_industry' actions to save the image field.
        add_action('created_domain_industry', array($this, 'save_taxonomy_image'), 10, 2);
        add_action('edited_domain_industry', array($this, 'save_taxonomy_image'), 10, 2);

        // Repeat for 'domain_cat' taxonomy.
        add_action('domain_cat_add_form_fields', array($this, 'add_taxonomy_image_field'));
        add_action('domain_cat_edit_form_fields', array($this, 'edit_taxonomy_image_field'));

        add_action('created_domain_cat', array($this, 'save_taxonomy_image'), 10, 2);
        add_action('edited_domain_cat', array($this, 'save_taxonomy_image'), 10, 2);
    }

    // Method to register the custom post type.

    /**
     * 
     * function for registering domain post type.
     * @return void
     */
    public function register_domain_post_type()
    {
        // Labels array for the post type.
        $labels = array(
            'name'                  => _x('Domains', 'Post type general name', 'webstarter'),
            'singular_name'         => _x('Domain', 'Post type singular name', 'webstarter'),
            'menu_name'             => _x('Domains', 'Admin Menu text', 'webstarter'),
            'name_admin_bar'        => _x('Domain', 'Add New on Toolbar', 'webstarter'),
            'add_new'               => __('Add New', 'webstarter'),
            'add_new_item'          => __('Add New Domain', 'webstarter'),
            'new_item'              => __('New Domain', 'webstarter'),
            'edit_item'             => __('Edit Domain', 'webstarter'),
            'view_item'             => __('View Domain', 'webstarter'),
            'all_items'             => __('All Domains', 'webstarter'),
            'search_items'          => __('Search Domains', 'webstarter'),
            'not_found'             => __('No domains found.', 'webstarter'),
            'not_found_in_trash'    => __('No domains found in Trash.', 'webstarter'),
            'featured_image'        => _x('Domain Image', 'Overrides the “Featured Image” phrase', 'webstarter'),
            'set_featured_image'    => _x('Set domain image', 'Overrides the “Set featured image” phrase', 'webstarter'),
            'remove_featured_image' => _x('Remove domain image', 'Overrides the “Remove featured image” phrase', 'webstarter'),
            'use_featured_image'    => _x('Use as domain image', 'Overrides the “Use as featured image” phrase', 'webstarter'),
            'archives'              => _x('Domain archives', 'The post type archive label', 'webstarter'),
            'insert_into_item'      => _x('Insert into domain', 'Overrides the “Insert into post” phrase', 'webstarter'),
            'uploaded_to_this_item' => _x('Uploaded to this domain', 'Overrides the “Uploaded to this post” phrase', 'webstarter'),
            'filter_items_list'     => _x('Filter domains list', 'Screen reader text for the filter links heading', 'webstarter'),
            'items_list_navigation' => _x('Domains list navigation', 'Screen reader text for the pagination heading', 'webstarter'),
            'items_list'            => _x('Domains list', 'Screen reader text for the items list heading', 'webstarter'),
        );

        // Arguments array for the post type.
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'domain'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'author', 'thumbnail'),
            'show_in_rest'       => true,
        );

        // Registers the custom post type.
        register_post_type('domain', $args);
    }

    /**
     * Function for regestring taxonomy for domain post type
     * @return void
     */
    public function register_domain_taxonomies()
    {
        // Labels for the "Industries" taxonomy.
        $industry_labels = array(
            'name'              => _x('Industries', 'taxonomy general name', 'webstarter'),
            'singular_name'     => _x('Industry', 'taxonomy singular name', 'webstarter'),
            'search_items'      => __('Search Industries', 'webstarter'),
            'all_items'         => __('All Industries', 'webstarter'),
            'parent_item'       => __('Parent Industry', 'webstarter'),
            'parent_item_colon' => __('Parent Industry:', 'webstarter'),
            'edit_item'         => __('Edit Industry', 'webstarter'),
            'update_item'       => __('Update Industry', 'webstarter'),
            'add_new_item'      => __('Add New Industry', 'webstarter'),
            'new_item_name'     => __('New Industry Name', 'webstarter'),
            'menu_name'         => __('Industries', 'webstarter'),
        );

        // Arguments for the "Industries" taxonomy.
        $industry_args = array(
            'hierarchical'      => true,
            'labels'            => $industry_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'domain-industry'),
            'show_in_rest'      => true
        );

        // Register the "Industries" taxonomy.
        register_taxonomy('domain_industry', array('domain'), $industry_args);

        // Labels for the "Categories" taxonomy.
        $category_labels = array(
            'name'              => _x('Categories', 'taxonomy general name', 'webstarter'),
            'singular_name'     => _x('Category', 'taxonomy singular name', 'webstarter'),
            'search_items'      => __('Search Categories', 'webstarter'),
            'all_items'         => __('All Categories', 'webstarter'),
            'parent_item'       => __('Parent Category', 'webstarter'),
            'parent_item_colon' => __('Parent Category:', 'webstarter'),
            'edit_item'         => __('Edit Category', 'webstarter'),
            'update_item'       => __('Update Category', 'webstarter'),
            'add_new_item'      => __('Add New Category', 'webstarter'),
            'new_item_name'     => __('New Category Name', 'webstarter'),
            'menu_name'         => __('Categories', 'webstarter'),
        );

        // Arguments for the "Categories" taxonomy.
        $category_args = array(
            'hierarchical'      => true,
            'labels'            => $category_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'domain-cat'),
            'show_in_rest'      => true
        );

        // Register the "Categories" taxonomy.
        register_taxonomy('domain_cat', array('domain'), $category_args);

        // Labels for the "Tags" taxonomy.
        $tags_label = array(
            'name'              => _x('Tags', 'taxonomy general name', 'webstarter'),
            'singular_name'     => _x('Tag', 'taxonomy singular name', 'webstarter'),
            'search_items'      => __('Search Tags', 'webstarter'),
            'all_items'         => __('All Tags', 'webstarter'),
            'edit_item'         => __('Edit Tag', 'webstarter'),
            'update_item'       => __('Update Tag', 'webstarter'),
            'add_new_item'      => __('Add New Tag', 'webstarter'),
            'new_item_name'     => __('New Tag Name', 'webstarter'),
            'menu_name'         => __('Tags', 'webstarter'),
        );

        // Arguments for the "Tags" taxonomy.
        $tags = array(
            'hierarchical'      => false,
            'labels'            => $tags_label,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'domain-tag'),
            'show_in_rest'      => true
        );

        // Register the "Tags" taxonomy.
        register_taxonomy('domain_tag', array('domain'), $tags);
    }

    /**
     * Method to add image field to the  industry and category taxonomy.
     * @param mixed $taxonomy
     * @return void
     */
    public function add_taxonomy_image_field($taxonomy)
    {
?>
        <div class="form-field term-group">
            <td>
                <label>Is Popular</label>
                <input type="checkbox" name="is_popular">
            </td>
        </div>
        <div class="form-field term-group">
            <label for="taxonomy-image-id"><?php _e('Image', 'webstarter'); ?></label>
            <input type="hidden" id="taxonomy-image-id" name="taxonomy-image-id" value="">
            <div id="taxonomy-image-wrapper"></div>
            <p>
                <input type="button" class="button button-secondary" id="taxonomy-image-upload-button" value="<?php _e('Add Image', 'webstarter'); ?>">
                <input type="button" class="button button-secondary" id="taxonomy-image-remove-button" value="<?php _e('Remove Image', 'webstarter'); ?>">
            </p>
        </div>
    <?php
    }

    // Method to add the image field to the "Edit" taxonomy form.
    public function edit_taxonomy_image_field($term)
    {
        // Retrieve the existing value(s) for the term meta.
        $image_id = get_term_meta($term->term_id, 'taxonomy-image-id', true);
        $is_popular = get_term_meta($term->term_id, '_is_popular', true);
        
    ?>
        <tr class="form-field term-group-wrap">
            <td>
                <label>Is Popular</label>
                <input type="checkbox" name="is_popular" <?php echo $is_popular ? 'checked' : '' ?>>
            </td>
        </tr>

        </tr>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="taxonomy-image-id"><?php _e('Image', 'webstarter'); ?></label>
            </th>
            <td>
                <input type="hidden" id="taxonomy-image-id" name="taxonomy-image-id" value="<?php echo esc_attr($image_id); ?>">
                <div id="taxonomy-image-wrapper">
                    <?php if ($image_id) { ?>
                        <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                    <?php } ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary" id="taxonomy-image-upload-button" value="<?php _e('Add Image', 'webstarter'); ?>">
                    <input type="button" class="button button-secondary" id="taxonomy-image-remove-button" value="<?php _e('Remove Image', 'webstarter'); ?>">
                </p>
            </td>
        </tr>
<?php
    }

    /**
     * Method for saving taxonomy
     */
    public function save_taxonomy_image($term_id, $tt_id)
    {
        if (isset($_POST['is_popular']) && $_POST['is_popular']) {
            update_term_meta($term_id, '_is_popular', $_POST['is_popular']);
        }
        if (isset($_POST['taxonomy-image-id']) && '' !== $_POST['taxonomy-image-id']) {
            $image = intval($_POST['taxonomy-image-id']);
            update_term_meta($term_id, 'taxonomy-image-id', $image);
        } else {
            delete_term_meta($term_id, 'taxonomy-image-id');
        }
    }

    function create_domain_order_post_type()
    {
        $labels = array(
            'name'               => _x('Domain Orders', 'post type general name', 'webstarter'),
            'singular_name'      => _x('Domain Order', 'post type singular name', 'webstarter'),
            'menu_name'          => _x('Domain Orders', 'admin menu', 'webstarter'),
            'name_admin_bar'     => _x('Domain Order', 'add new on admin bar', 'webstarter'),
            'add_new'            => _x('Add New', 'domain_order', 'webstarter'),
            'add_new_item'       => __('Add New Order', 'webstarter'),
            'new_item'           => __('New Domain Order', 'webstarter'),
            'edit_item'          => __('Edit Domain Order', 'webstarter'),
            'view_item'          => __('View Domain Order', 'webstarter'),
            'all_items'          => __('All Orders', 'webstarter'),
            'search_items'       => __('Search Domain Orders', 'webstarter'),
            'parent_item_colon'  => __('Parent Domain Orders:', 'webstarter'),
            'not_found'          => __('No domain orders found.', 'webstarter'),
            'not_found_in_trash' => __('No domain orders found in Trash.', 'webstarter')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'domain_order'),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 58,
            'supports'           => array('title'),
            'menu_icon'          => 'dashicons-admin-post',  // You can change this to a different icon if needed
            'show_in_rest'      => true
        );

        register_post_type('domain_order', $args);
    }


    // Adding post type faq 
    function create_faq_post_type()
    {
        $labels = array(
            'name'               => _x('FAQs', 'Post Type General Name', 'webstarter'),
            'singular_name'      => _x('FAQ', 'Post Type Singular Name', 'webstarter'),
            'menu_name'          => __('FAQs', 'webstarter'),
            'name_admin_bar'     => __('FAQ', 'webstarter'),
            'add_new_item'       => __('Add New FAQ', 'webstarter'),
            'new_item'           => __('New FAQ', 'webstarter'),
            'edit_item'          => __('Edit FAQ', 'webstarter'),
            'view_item'          => __('View FAQ', 'webstarter'),
            'all_items'          => __('All FAQs', 'webstarter'),
            'search_items'       => __('Search FAQs', 'webstarter'),
            'not_found'          => __('No FAQs found.', 'webstarter'),
            'not_found_in_trash' => __('No FAQs found in Trash.', 'webstarter'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'faq'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor'),
            'show_in_rest' => true,
        );

        register_post_type('faq', $args);
    }

    function create_faq_cat_taxonomy()
    {
        $labels = array(
            'name'              => _x('FAQ Categories', 'taxonomy general name', 'webstarter'),
            'singular_name'     => _x('FAQ Category', 'taxonomy singular name', 'webstarter'),
            'search_items'      => __('Search FAQ Categories', 'webstarter'),
            'all_items'         => __('All FAQ Categories', 'webstarter'),
            'parent_item'       => __('Parent FAQ Category', 'webstarter'),
            'parent_item_colon' => __('Parent FAQ Category:', 'webstarter'),
            'edit_item'         => __('Edit FAQ Category', 'webstarter'),
            'update_item'       => __('Update FAQ Category', 'webstarter'),
            'add_new_item'      => __('Add New FAQ Category', 'webstarter'),
            'new_item_name'     => __('New FAQ Category Name', 'webstarter'),
            'menu_name'         => __('FAQ Categories', 'webstarter'),
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true, // Like categories
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'faq-category'),
            'show_in_rest'   => true,
        );

        register_taxonomy('faq_cat', array('faq'), $args);
    }
}
// Instantiate the class to create the post type.
new wstr_post_types();
