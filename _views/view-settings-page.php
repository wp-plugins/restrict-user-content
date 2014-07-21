<?php do_action("{$this->_pagename}_plugin_save_options"); ?>
<div class="wrap">
    <?php screen_icon(); ?>
    <h2><?php esc_html_e( apply_filters("{$this->_pagename}_settings_page_title", 'Plugin Page Title') ); ?></h2>
    <form name="my_form" method="post">
        <input type="hidden" name="action" value="some-action">
        <?php wp_nonce_field( "{$this->_pagename}_save_settings", "{$this->_pagename}_settings_nonce" );
        /* Used to save closed meta boxes and their order */
        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
 
        <div id="poststuff">
 
            <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
 
                <div id="post-body-content">
                    <!-- #post-body-content -->
                </div>
 
                <div id="postbox-container-1" class="postbox-container">
                    <?php do_meta_boxes('','side',null); ?>
                </div>
 
                <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes('','normal',null); ?>
                    <?php do_meta_boxes('','advanced',null); ?>
                </div>
 
            </div> <!-- #post-body -->
 
        </div> <!-- #poststuff -->
 
    </form>
 
</div><!-- .wrap -->