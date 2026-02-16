<?php

/********************************* 
 * display content functions
**********************************/

add_shortcode('facilities', 'facamen_render_shortcode');

add_filter('body_class', function($classes) {
    $classes[] = 'facamen-plugin-active';
    return $classes;
});

function facamen_render_shortcode() {
	// Prevent showing on the blog index
    if ( is_home() && ! is_singular() ) {
        return ''; // Return nothing
    }
	
    ob_start();
    
    // fullwidth wrapper
    ?>
    <div class="facamen-fullwidth-wrapper">
		<div id="ficDiv" class="facamen-inner-container">
    <?php
    // Main Section - Top
    $top_title = esc_html(facamen_get_option('top_title', ''));
    $top_description = esc_html(facamen_get_option('top_description', ''));

    // Main Section - Bottom
    $bottom_title = esc_html(facamen_get_option('bottom_title', ''));
    $bottom_description = esc_html(facamen_get_option('bottom_description', '')); 
    $bottom_buttontext = esc_html(facamen_get_option('bottom_buttontext', ''));
    $bottom_buttonlinkurl = esc_html(facamen_get_option('bottom_buttonlinkurl', ''));
    $open_new_tab = facamen_get_option('open_new_tab', 'yes');
    $bottom_two_title = esc_html(facamen_get_option('bottom_two_title', ''));
    $bottom_two_description = esc_html(facamen_get_option('bottom_two_description', ''));    
    $bottom_two_buttontext = esc_html(facamen_get_option('bottom_two_buttontext', ''));
    $bottom_two_buttonlinkurl = esc_html(facamen_get_option('bottom_two_buttonlinkurl', ''));
    $open_two_new_tab = facamen_get_option('open_two_new_tab', 'yes');
    $two_title_color = facamen_get_option('bottom_two_title_color', '#000000');
    $two_desc_color = facamen_get_option('bottom_two_description_color', '#000000');
    $backgroundTwoColor = facamen_get_option('backgroundTwoColor', '#ffffff');
    $backgroundOverlay = facamen_get_option('backgroundOverlay', '#000000');
    $apply_overlay = facamen_get_option('apply_overlay', 'no');

    // Get categories + options from DB
    $categories = facamen_get_categories_with_options();

    $showanimation = facamen_get_option('showanimation', 'yes');
    ?>
    <!-- Main Section - Top -->    
    <div class="centered">
        <h2 id="elbottom" class="wp-block-heading <?php echo ($showanimation === 'yes') ? 'fade-in-down' : ''; ?>">
            <?php echo $top_title; ?>
        </h2>
        <p id="eltop" class="wp-block-paragraph <?php echo ($showanimation === 'yes') ? 'fade-in-up' : ''; ?>">
            <?php echo $top_description; ?>
        </p>
    </div>
    <?php
        
   // Track categories by ID (to group options per category)
    $grouped = [];
    foreach ($categories as $row) {
        // Only include categories where enable = 'yes'
        if ($row->enable !== 'yes') {
            continue;
        }

        if (!isset($grouped[$row->id])) {
            $grouped[$row->id] = [
                'id' => $row->id,
                'title' => $row->title,
                'buttontext' => $row->buttontext,
                'buttonlinkurl' => $row->buttonlinkurl,
                'imagelinkurl' => $row->imagelinkurl,
                'opennewtab' => isset($row->opennewtab) ? $row->opennewtab : 'no',
                /* 'opennewtab' => $row->opennewtab, */
                'options' => []
            ];
        }
        // if ($row->option_id) {
        if (isset($row->option_id)) {
            $grouped[$row->id]['options'][] = [
                'name' => $row->option_name,
                'value' => $row->option_value
            ];
        }
    }  

    // Animation toggle
    $showanimation = facamen_get_option('showanimation', 'yes');

    // Loop categories and render odd/even differently  
    $layout_index = 0;

    foreach ($grouped as $cat) {
        // Determine image class
        $img = $cat['imagelinkurl'] ?? '';
        $image_class = (strpos($img, 'spacer.png') !== false)
            ? 'image-right wp-block-image'
            : 'image-right-border wp-block-image';

        // Prepare animation or plain classes
        $container_class_even = $showanimation === 'yes' ? 'container scroll-down' : 'container';
        $container_class_odd  = $showanimation === 'yes' ? 'container reverse' : 'container reverse';
        $text_class_even      = $showanimation === 'yes' ? 'text-content' : 'text-content';
        $text_class_odd       = $showanimation === 'yes' ? 'text-content' : 'text-content';
        $image_class_right    = $showanimation === 'yes' ? $image_class . ' slide-in-right' : $image_class;
        $image_class_left     = $showanimation === 'yes' ? $image_class . ' slide-in-left' : $image_class;

        // Alternate layout by layout_index, not ID
        if ($layout_index % 2 === 0): ?>
            <div class="<?php echo esc_attr($container_class_even); ?>">
                <div class="<?php echo esc_attr($text_class_even); ?>">
                    <h2 class="wp-block-heading left-align"><?php echo esc_html($cat['title']); ?></h2>
                    <?php if (!empty($cat['options'])): ?>
                        <ul class="list-with-icons wp-block-list">
                            <?php foreach ($cat['options'] as $opt): ?>
                                <li><i class="<?php echo esc_attr($opt['name']); ?>"></i> <?php echo esc_html($opt['value']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($cat['buttontext'] && $cat['buttonlinkurl']): ?>
                        <div class="wp-block-button">
                            <a href="<?php echo esc_url($cat['buttonlinkurl']); ?>"
                            class="wp-block-button__link"
                            <?php echo ($cat['opennewtab'] === 'yes') ? 'target="_blank" rel="noopener"' : ''; ?>>
                                <?php echo esc_html($cat['buttontext']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($cat['imagelinkurl']): ?>
                    <img src="<?php echo esc_url($cat['imagelinkurl']); ?>"
                        alt="<?php echo esc_attr($cat['title']); ?>"
                        class="<?php echo esc_attr($image_class_right); ?>" />
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="<?php echo esc_attr($container_class_odd); ?>">
                <?php if ($cat['imagelinkurl']): ?>
                    <img src="<?php echo esc_url($cat['imagelinkurl']); ?>"
                        alt="<?php echo esc_attr($cat['title']); ?>"
                        class="<?php echo esc_attr($image_class_left); ?>" />
                <?php endif; ?>
                <div class="<?php echo esc_attr($text_class_odd); ?> <?php echo $showanimation === 'yes' ? 'scroll-up' : ''; ?>">
                    <h2 class="wp-block-heading left-align"><?php echo esc_html($cat['title']); ?></h2>
                    <?php if (!empty($cat['options'])): ?>
                        <ul class="list-with-icons wp-block-list">
                            <?php foreach ($cat['options'] as $opt): ?>
                                <li><i class="<?php echo esc_attr($opt['name']); ?>"></i> <?php echo esc_html($opt['value']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($cat['buttontext'] && $cat['buttonlinkurl']): ?>
                        <div class="wp-block-button">
                            <a href="<?php echo esc_url($cat['buttonlinkurl']); ?>"
                            class="wp-block-button__link"
                            <?php echo ($cat['opennewtab'] === 'yes') ? 'target="_blank" rel="noopener"' : ''; ?>>
                                <?php echo esc_html($cat['buttontext']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif;

        $layout_index++;
    }

    $bottom_visible = facamen_get_option('bottom_visible', 'yes');
    $bottom_two_visible = facamen_get_option('bottom_two_visible', 'yes');

    // Main Section - Bottom
    /* Content Section One */
    if ($bottom_visible === 'yes' && ($bottom_title || $bottom_buttontext)): ?>
        <div class="bottom-container">
            <h2 class="wp-block-heading"><?php echo $bottom_title; ?></h2>
            <h5><?php echo $bottom_description; ?></h5>
            <?php if ($bottom_buttontext && $bottom_buttonlinkurl): ?>
                <div class="wp-block-button">
                    <a href="<?php echo esc_url($bottom_buttonlinkurl); ?>" 
                        class="wp-block-button__link"
                        <?php echo ($open_new_tab === 'yes') ? 'target="_blank" rel="noopener"' : ''; ?>>
                        <?php echo esc_html($bottom_buttontext); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
		</div>
        <?php 
        /* Content Section Two */
        if ($bottom_two_visible === 'yes' && ($bottom_two_title) && ($bottom_two_description)): 
            $bottom_two_height = intval(facamen_get_option('bottom_two_height', 250));
            $bottom_two_bg = esc_url(facamen_get_option('bottom_two_backgroundImage', ''));

            $inline_style = "height: {$bottom_two_height}px;";

            // Background image or fallback color
            if (!empty($bottom_two_bg)) {
                $inline_style .= " background-image: url('{$bottom_two_bg}'); 
                                background-size: cover; 
                                background-position: center;";
            } else {
                $inline_style .= " background-color: {$backgroundTwoColor};";
            }            
        ?>
            <div class="abovefooter-container" 
                style="<?php echo esc_attr($inline_style); ?> 
                        position: relative; overflow:hidden;">

                <?php if ($apply_overlay === 'yes'): ?>
                    <div class="facamen-overlay"
                        style="
                            position:absolute;
                            top:0;
                            left:0;
                            width:100%;
                            height:100%;
                            background: <?php echo esc_attr($backgroundOverlay); ?>;
                            opacity:<?php echo esc_attr(facamen_get_option('background_overlay_opacity', '0.45')); ?>;
                            pointer-events:none;
                        ">
                    </div>
                <?php endif; ?>
                <div class="abovefooter-content">
                <h2 class="wp-block-heading" 
                    style="color: <?php echo esc_attr($two_title_color); ?>;">
                    <?php echo $bottom_two_title; ?>                   
                </h2>
                <h5 style="color: <?php echo esc_attr($two_desc_color); ?>;">
                    <?php echo $bottom_two_description; ?>                    
                </h5>
                <?php if ($bottom_two_buttontext): ?>
                <div class="wp-block-button">
                    <a href="<?php echo esc_url($bottom_two_buttonlinkurl); ?>" 
                        class="wp-block-button__link"
                        <?php echo ($open_two_new_tab === 'yes') ? 'target="_blank" rel="noopener"' : ''; ?>>
                        <?php echo esc_html($bottom_two_buttontext); ?>
                    </a>                    
                </div>
                <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php  
    return ob_get_clean();
}