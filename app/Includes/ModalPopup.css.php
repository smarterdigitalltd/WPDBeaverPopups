<?php

// Set background colour or image for modals

if ( isset( $settings->popup_type ) && 'modal' === $settings->popup_type ) :
    if ( 'color' == $settings->modal_overlay_background_type && isset( $settings->modal_overlay_background_color ) ) : ?>
        .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active, .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active .<?php echo $cpt; ?>__outer,
        #<?php echo $cpt . '-' . $popup->ID; ?>-overlay {
            background-color: <?php echo false === strpos( $settings->modal_overlay_background_color, 'rgba' ) ? '#' . $settings->modal_overlay_background_color : $settings->modal_overlay_background_color; ?>;
        }
    <?php endif; ?>

    <?php if ( 'image' == $settings->modal_overlay_background_type && isset( $settings->modal_overlay_background_image ) ) : ?>
        .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active,
        #<?php echo $cpt . '-' . $popup->ID; ?>-overlay {
            background-color: transparent;
            background-image: url(<?php echo $settings->modal_overlay_background_image_src; ?>);
            background-position: center;
            background-repeat: <?php echo isset( $settings->modal_overlay_background_image_repeat ) && 'repeat' == $settings->modal_overlay_background_image_repeat ? 'repeat' : 'no-repeat'; ?>;
            background-size: <?php echo isset( $settings->modal_overlay_background_image_size ) ? $settings->modal_overlay_background_image_size : 'cover'; ?>;
        }
    <?php endif; ?>

    <?php // height - in beaver builder ?>

    .fl-builder-edit .single-<?php echo $cpt; ?> #<?php echo $cpt . '-' . $popup->ID; ?>__content .fl-builder-content {
        max-height: <?php echo isset( $settings->height ) && ! empty( $settings->height ) ?
                $settings->height . 'px;' :
                'calc(100vh - 73px);'; ?>
    }

    <?php // height - in single ?>

    html:not(.fl-builder-edit) .admin-bar.single-<?php echo $cpt; ?> #<?php echo $cpt . '-' . $popup->ID; ?>__content .fl-builder-content {
        max-height: <?php echo isset( $settings->height ) && ! empty( $settings->height ) ?
                $settings->height . 'px;' :
                'calc(100vh - 32px);'; ?>
    }
<?php endif; ?>
