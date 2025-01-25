<?php
/*
Plugin Name: Engrave Jewellery
Description: Custom plugin for customers to add engraving to jewellery and preview the outcome.
Textdomain: enjwlr
Version: 1.0
Author: DET E ONUR LÄÄÄN Fast FAKTISKT ÖMER LÄÄÄÄÄÄÄÄÄN
*/


class EngraveJewelleryPlugin
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_scripts'));
        add_action('add_meta_boxes', array($this, 'add_engravable_checkbox_to_sidebar'));
        add_action('save_post', array($this, 'save_engravable_checkbox'));
        add_action('product_cat_add_form_fields', array($this, 'add_category_engraving_preview_image_field'), 10, 2);
        add_action('product_cat_edit_form_fields', array($this, 'add_category_engraving_preview_image_field'), 10, 2);
        add_action('edited_product_cat', array($this, 'save_category_engraving_preview_image'), 10, 2);
        add_action('create_product_cat', array($this, 'save_category_engraving_preview_image'), 10, 2);
        add_action('woocommerce_single_product_summary', array($this, 'add_engrave_button_to_single_product'), 25);
        add_shortcode('engraving', array($this, 'engraving_shortcode'));
        add_action('woocommerce_add_to_cart', array($this, 'add_engraving_to_cart'), 10, 6);
        add_filter('woocommerce_add_cart_item_data', array($this, 'enjwlr_add_item_data'), 10, 2);
        add_filter('woocommerce_get_item_data', array($this, 'enjwlr_get_item_data'), 10, 2);
        add_action('woocommerce_cart_calculate_fees', array($this, 'add_extra_fee_to_product'), 10, 1);
        add_action('woocommerce_widget_shopping_cart_total', array($this, 'show_extra_fee_minicart'), 10, 1);
        add_action('woocommerce_after_order_itemmeta', array($this, 'display_meta_woocommerce_order'), 10, 3);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'store_engraving_info_in_order'), 10, 4);
        add_action('woocommerce_order_item_meta_end', array($this, 'add_meta_to_thankyou'), 10, 3);
    }

    public function enqueue_custom_scripts()
    {
        // Enqueue the JavaScript file for engraving.js
        wp_enqueue_script('engraving-script', plugin_dir_url(__FILE__) . 'js/engraving.js', array('jquery'), '1.0', true);

        // Localize script to pass data to JavaScript (from engraving.js)
        wp_localize_script(
            'engraving-script',
            'engraving_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('engraving_nonce')
            )
        );

        // Enqueue the JavaScript file for custom-engrave.js
        wp_enqueue_script('custom-engrave', plugin_dir_url(__FILE__) . 'js/custom-engrave.js', array('jquery'), '1.0', true);

        // Enqueue any associated CSS file for custom-engrave-styles.css
        wp_enqueue_style('custom-engrave-styles', plugin_dir_url(__FILE__) . 'css/custom-engrave-styles.css');
    }


    public function add_engravable_checkbox_to_sidebar()
    {
        global $post, $woocommerce;

        // Check if this is a product edit page
        if ('product' === $post->post_type) {
            add_meta_box(
                'engravable_options',
                __('Engravable', 'woocommerce'),
                'display_engravable_checkbox',
                'product',
                'side', // Position in the sidebar
                'default'
            );
        }
    }



    // Display Engravable Checkbox
    public function display_engravable_checkbox()
    {
        global $post;

        // Check if the product is engravable
        $engravable = get_post_meta($post->ID, '_engravable', true);

        echo '<div class="options_group">';

        woocommerce_wp_checkbox(
            array(
                'id' => '_engravable',
                'label' => __('Engravable', 'woocommerce'),
                'description' => __('Check this box if the product is engravable', 'woocommerce'),
                'desc_tip' => 'true',
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => '_engraving_fee',
                'label' => __('Engraving Fee', 'woocommerce'),
                'description' => __('Enter the engraving fee', 'woocommerce'),
                'desc_tip' => 'true',
                'type' => 'number',
                'custom_attributes' => array(
                    'step' => 'any',
                    'min' => '0'
                )
            )
        );
        echo '</div>';
    }



    public function save_engravable_checkbox($post_id)
    {
        // Check if it's an autosave
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        // Check if the '_engrave_text_option' checkbox is checked
        $engrave_text_option = isset($_POST['_engravable']) ? 'yes' : 'no';

        // Update the '_engravable' custom field with the checkbox value
        update_post_meta($post_id, '_engravable', $engrave_text_option);
        update_post_meta($post_id, '_engraving_fee', $_POST['_engraving_fee']);
    }


    public function add_category_engraving_preview_image_field($tag)
    {
        $engraving_preview_image = get_term_meta($tag->term_id, 'category_engraving_preview_image', true);

        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="category_engraving_preview_image">
                    <?php _e('Engraving Preview Image', 'woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="hidden" id="category_engraving_preview_image" name="category_engraving_preview_image"
                    value="<?php echo esc_attr($engraving_preview_image); ?>">
                <img src="<?php echo esc_url(wp_get_attachment_url($engraving_preview_image)); ?>"
                    style="max-width: 100px; max-height: 100px;">
                <button class="button button-secondary" id="upload_category_engraving_preview_image">
                    <?php _e('Upload Image', 'woocommerce'); ?>
                </button>
                <button class="button button-secondary" id="remove_category_engraving_preview_image">
                    <?php _e('Remove Image', 'woocommerce'); ?>
                </button>
            </td>
        </tr>
        <script>
            jQuery(document).ready(function ($) {

                // Function to handle engraving engrave-button
                function handlePreview() {
                    var engravingText = $('#engraving-text').val();
                    var engravingStyle = $('#engraving-style').val();
                    var $engravingPreview = $('#engraving-preview'); // The container for the preview

                    // Update the preview text and style
                    $engravingPreview.text(engravingText);
                    $engravingPreview.css('color', engravingStyle);
                }

                // Attach a click event handler to the "Preview" button
                $('#preview-button').on('click', function () {
                    handlePreview();
                });

                // Upload category engraving preview image
                $('#upload_category_engraving_preview_image').on('click', function (e) {
                    e.preventDefault();
                    var image = wp.media({
                        title: 'Upload Image',
                        multiple: false
                    }).open()
                        .on('select', function (e) {
                            var uploadedImage = image.state().get('selection').first();
                            var imageId = uploadedImage.id;
                            $('#category_engraving_preview_image').val(imageId);
                            $('img').attr('src', uploadedImage.attributes.url);
                        });
                });

                // Remove category engraving preview image
                $('#remove_category_engraving_preview_image').on('click', function (e) {
                    e.preventDefault();
                    $('#category_engraving_preview_image').val('');
                    $('img').attr('src', '');
                });
            });
        </script>
        <?php
    }

    // Save Category Engraving Preview Image
    public function save_category_engraving_preview_image($term_id)
    {
        if (isset($_POST['category_engraving_preview_image'])) {
            update_term_meta($term_id, 'category_engraving_preview_image', $_POST['category_engraving_preview_image']);
        }
    }



    //__________________________________________________________
    // Add "Engrave" button to single product pages for engravable products
    public function add_engrave_button_to_single_product()
    {
        if (is_product()) {
            global $post;
            $is_engravable = get_post_meta($post->ID, '_engravable', true);
            if ($is_engravable === 'yes') {
                echo '<button type="button" id="engrave-button" class="single_add_to_cart_button engrave-button">Engrave</button>';
                echo '<div class="engrave-info"></div>';
                // Output the engraving section directly below the button (optional)
                echo do_shortcode('[engraving]');
            }
        }
    }


    public function engraving_shortcode($atts)
    {
        // Check if this is a product page
        if (is_product()) {
            $product_id = get_the_ID();
            $engravable = get_post_meta($product_id, '_engravable', true);

            // Check if the product is engravable
            if ($engravable === 'yes') {
                // Output the HTML for the engraving section
                $product_categories = get_the_terms($product_id, 'product_cat');

                // Initialize a variable to store the category engraving preview image HTML
                $category_image_html = '';

                // Check if any of the categories have the engraving preview image
                foreach ($product_categories as $category) {
                    $category_id = $category->term_id;
                    $engraving_preview_image_id = get_term_meta($category_id, 'category_engraving_preview_image', true);

                    if ($engraving_preview_image_id) {
                        $category_image_html = wp_get_attachment_image($engraving_preview_image_id, 'medium');
                        break; // Stop after finding the first category with an engraving preview image
                    }
                }

                ob_start();
                ?>
                <div class="engraving-section" style="display: none;"> <!-- Hide the engraving section initially -->
                    <!-- Close button for the popup -->
                    <div id="engraving-popup-close" class="engraving-popup-close">✕</div>

                    <!-- Engraving Preview Image -->
                    <div class="inner-container">
                        <div class="container-image">
                            <div class="engraving-preview" style="position: relative;">
                                <?php echo $category_image_html; ?>
                                <!-- Engraving Preview Text -->
                                <div id="engraving-preview"
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #000; font-size: 18px;">
                                </div>
                            </div>
                        </div>
                        <!-- Engraving Input Fields -->
                        <div class="container-info">
                            <div class="wd-entities-title">Customise it to your liking</div>

                            <div class="engraving-input">
                                <input type="text" id="engraving-text" placeholder="Engraving Text">
                                <select id="engraving-style">
                                    <option value="arial">Handwriting</option>
                                    <option value="blue">Arial</option>
                                    <option value="green">OSV OSV</option>
                                </select>
                                <!-- Preview Button -->
                                <div class="button-wrapper">
                                    <button id="preview-button">PREVIEW</button>
                                    <button id="add-engraving-button">Add</button>
                                    <button id="exit-engraving-button">Cancel</button>
                                </div>
                                <p>We may need a couple of extra days for engraved items to be finalized and shipped out.</p>
                                <p>Remember that engraved jewellery is consider
                                    final sale therefore can not be return or exchanged.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- "Engrave" Button -->
                <!-- <button id="engrave-button">Engrave</button> -->
                <script>
                    jQuery(document).ready(function ($) {
                        // Function to handle the "Engrave" button click event
                        $('#engrave-button').on('click', function () {
                            // Show the engraving section
                            $('.engraving-section').fadeIn();
                        });
                    });
                </script>
                <?php
                return ob_get_clean();
            }
        }
    }






    public function add_engraving_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        // Check if the engraving text and style were posted
        if (isset($_POST['engraving-text']) && isset($_POST['engraving-style'])) {
            $engraving_text = sanitize_text_field($_POST['engraving-text']);
            $engraving_style = sanitize_text_field($_POST['engraving-style']);


            // Update the cart item data
            WC()->cart->cart_contents[$cart_item_key]['engraving'] = [
                'text' => $engraving_text,
                'style' => $engraving_style,
                'fee' => get_post_meta($product_id, '_engraving_fee', true),
            ];

        }
    }


    public function enjwlr_add_item_data($cart_item_data)
    {
        if (isset($cart_item_data['engraving']['text']) && isset($cart_item_data['engraving']['style'])) {

            $engrave_fee = get_post_meta($cart_item_data['product_id'], '_engraving_fee', true);

            $item_data['engraving'] = [
                'text' => $cart_item_data['engraving_text'],
                'style' => $cart_item_data['engraving_style'],
                'fee' => $engrave_fee,
            ];
        }
        return $item_data;
    }

    public function enjwlr_get_item_data($item_data, $cart_item_data)
    {
        if (isset($cart_item_data['engraving']['text']) && isset($cart_item_data['engraving']['style'])) {
            $item_data[] = array(
                'key' => __('Engraving', 'enjwlr'),
                'value' => wc_clean($cart_item_data['engraving']['text']),
            );
            $item_data[] = array(
                'key' => __('Style', 'enjwlr'),
                'value' => wc_clean($cart_item_data['engraving']['style']),
            );
            $engrave_fee = get_post_meta($cart_item_data['product_id'], '_engraving_fee', true);
            $item_data[] = [
                'key' => __('Fee', 'enjwlr'),
                'value' => wc_clean((empty($engrave_fee) ? 'Free of charge' : wc_price($engrave_fee))),
            ];
        }
        return $item_data;
    }

    public function add_extra_fee_to_product($cart)
    {
        $engraving_fee = 0;

        foreach ($cart->get_cart() as $item => $values) {

            $price = $values['engraving']['fee'];
            $engraving_fee = 0;
            if (empty($price))
                continue;

            $engraving_fee += $price;

        }
        $cart->add_fee('Engraving Fee', $engraving_fee, true, 'standard');

    }

    public function show_extra_fee_minicart()
    {
        global $woocommerce;
        $cart = $woocommerce->cart->get_cart();
        $fee = 0;
        foreach ($cart as $cart_item) {

            $price = $cart_item['engraving']['fee'];
            $fee += $price;
        }
        echo '<strong>' . __('Engraving Fee', 'enjwlr') . ':</strong> <span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' . wc_price($fee) . '</bdi></span>			
                    </p><p class="woocommerce-mini-cart__total total">';

    }



    // Add your plugin public functionality here

    public function display_meta_woocommerce_order($item_id, $item, $product)
    {
        $engraving = $item->get_meta('_engraving');
        ?>
        <table cellspacing="0" class="display_meta">
            <tbody>
                <?php
                if (!empty($engraving['text'])) {
                    ?>
                    <tr>
                        <th>
                            <?= __('Text', 'enjwlr'); ?>
                        </th>
                        <td>
                            <?= $engraving['text']; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?= __('Style', 'enjwlr'); ?>
                        </th>
                        <td>
                            <?= $engraving['style']; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?= __('Fee', 'enjwlr'); ?>
                        </th>
                        <td>
                            <?= wc_price($engraving['fee']); ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>

            </tbody>
        </table>
        <?php

    }


    public function store_engraving_info_in_order($item, $cart_item_key, $values, $order)
    {
        if (isset($values['engraving']['text'])) {

            // Engraving text
            $item->add_meta_data('_engraving', $values['engraving']);
        }

    }


    public function add_meta_to_thankyou($item_id, $item, $order)
    {
        $engraving = $item->get_meta('_engraving');
        ?>
        <ul class="wc-item-meta engraving-list">
            <li>
                <strong class=" wc-item-meta-label">
                    <?= __('Text', 'enjwlr'); ?>
                </strong>
                <div class="wc-item-meta-value">
                    <?= $engraving['text']; ?>
                </div>
            </li>
            <li>
                <strong class="wc-item-meta-label">
                    <?= __('Style', 'enjwlr'); ?>
                </strong>
                <div class="wc-item-meta-value">
                    <?= $engraving['style']; ?>
                </div>
            </li>

        </ul>
        <?php

    }


}

// Initialize the plugin class
$engrave_jewellery_plugin = new EngraveJewelleryPlugin();

?>