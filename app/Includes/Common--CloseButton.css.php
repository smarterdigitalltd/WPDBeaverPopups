<?php use WPD\BeaverPopups\Helpers\Util; ?>

<?php // Positioning ?>

<?php if ( isset( $settings->modal_close_icon_position ) && 'box' === $settings->modal_close_icon_position || 'fly_out' === $settings->popup_type ) : ?>
    #<?php echo $cpt . '-' . $popup->ID; ?>__content {
        position: relative;
    }
<?php endif; ?>

<?php if ( isset( $settings->close_icon_vertical_distance ) && ! empty( $settings->close_icon_vertical_distance ) || '0' == $settings->close_icon_vertical_distance ) : ?>
    #<?php echo $cpt . '-' . $popup->ID; ?><?php echo isset( $settings->modal_close_icon_position ) && 'overlay' === $settings->modal_close_icon_position && 'modal' === $settings->popup_type ? '-overlay' : ''; ?> .jBox-closeButton {
        top: <?php echo $settings->close_icon_vertical_distance; ?>px;
    }
<?php endif; ?>

<?php if ( isset( $settings->close_icon_horizontal_distance ) && ! empty( $settings->close_icon_horizontal_distance ) || '0' == $settings->close_icon_horizontal_distance ) : ?>
    #<?php echo $cpt . '-' . $popup->ID; ?><?php echo isset( $settings->modal_close_icon_position ) && 'overlay' === $settings->modal_close_icon_position && 'modal' === $settings->popup_type ? '-overlay' : ''; ?> .jBox-closeButton {
        right: <?php echo $settings->close_icon_horizontal_distance; ?>px;
    }
<?php endif; ?>

<?php // admin bar override
if ( 'modal' === $settings->popup_type && 'overlay' === $settings->modal_close_icon_position ) : ?>
    .admin-bar:not(.fl-builder-edit) #<?php echo $cpt . '-' . $popup->ID; ?>-overlay .jBox-closeButton {
        top: <?php echo (int) $settings->close_icon_vertical_distance + 32; ?>px;
    }
<?php endif; ?>

<?php // Color ?>

<?php if ( isset( $settings->close_icon_color ) && ! empty( $settings->close_icon_color ) ) : ?>
    #<?php echo $cpt . '-' . $popup->ID; ?>-overlay .jBox-closeButton path,
    #<?php echo $cpt . '-' . $popup->ID; ?> .jBox-closeButton path {
        fill: <?php echo Util::ensureHex( $settings->close_icon_color, true ); ?>;
    }
<?php endif; ?>

<?php // Size ?>

<?php if ( isset( $settings->close_icon_size ) && ! empty( $settings->close_icon_size ) ) : ?>
    #<?php echo $cpt . '-' . $popup->ID; ?>-overlay .jBox-closeButton,
    #<?php echo $cpt . '-' . $popup->ID; ?> .jBox-closeButton {
        width: <?php echo $settings->close_icon_size; ?>px;
        height: <?php echo $settings->close_icon_size; ?>px;
    }
<?php endif; ?>

<?php // Size - tablet ?>

<?php if ( isset( $settings->close_icon_size_medium ) && ! empty( $settings->close_icon_size_medium ) ) : ?>
    @media (max-width: <?php echo $global_settings->medium_breakpoint; ?>px) {
        #<?php echo $cpt . '-' . $popup->ID; ?>-overlay .jBox-closeButton,
        #<?php echo $cpt . '-' . $popup->ID; ?> .jBox-closeButton {
            width: <?php echo $settings->close_icon_size_medium; ?>px;
            height: <?php echo $settings->close_icon_size_medium; ?>px;
        }
    }
<?php endif; ?>

<?php // Size - mobile ?>

<?php if ( isset( $settings->close_icon_size_responsive ) && ! empty( $settings->close_icon_size_responsive ) ) : ?>
    @media (max-width: <?php echo $global_settings->responsive_breakpoint; ?>px) {
        #<?php echo $cpt . '-' . $popup->ID; ?>-overlay .jBox-closeButton,
        #<?php echo $cpt . '-' . $popup->ID; ?> .jBox-closeButton {
            width: <?php echo $settings->close_icon_size_responsive; ?>px;
            height: <?php echo $settings->close_icon_size_responsive; ?>px;
        }
    }
<?php endif; ?>

<?php // CPT template ?>

<?php // Positioning ?>

<?php if ( 'modal' === $settings->popup_type && isset( $settings->modal_close_icon_position ) && 'overlay' === $settings->modal_close_icon_position ) : ?>
    .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active #<?php echo $cpt . '-' . $popup->ID; ?>__close-button {
        position: fixed;
    }

    :not(.fl-builder-show-admin-bar) body:not(.fl-builder-edit).single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active.admin-bar #<?php echo $cpt . '-' . $popup->ID; ?>__close-button {
        top: <?php echo (int) $settings->close_icon_vertical_distance + 32; ?>px;
    }
<?php endif; ?>

<?php if ( isset( $settings->modal_close_icon_position ) && 'box' === $settings->modal_close_icon_position || 'fly_out' === $settings->popup_type ) : ?>
    .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active #<?php echo $cpt . '-' . $popup->ID; ?>__close-button {
        position: absolute;
        z-index: 100008;
    }
<?php endif; ?>

<?php if ( isset( $settings->close_icon_vertical_distance ) && ! empty( $settings->close_icon_vertical_distance ) || '0' == $settings->close_icon_vertical_distance ) : ?>
    .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active #<?php echo $cpt . '-' . $popup->ID; ?>__close-button {
        top: <?php echo $settings->close_icon_vertical_distance; ?>px;
    }
<?php endif; ?>

<?php if ( isset( $settings->close_icon_horizontal_distance ) && ! empty( $settings->close_icon_horizontal_distance ) || '0' == $settings->close_icon_horizontal_distance ) : ?>
    .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active #<?php echo $cpt . '-' . $popup->ID; ?>__close-button {
        right: <?php echo $settings->close_icon_horizontal_distance; ?>px;
    }
<?php endif; ?>

<?php // Color ?>

.single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active #<?php echo $cpt . '-' . $popup->ID; ?>__close-button path {
    fill: <?php echo Util::ensureHex( $settings->close_icon_color, true ); ?>;
}

<?php // Size ?>

.single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active #<?php echo $cpt . '-' . $popup->ID; ?>__close-button {
    width: <?php echo isset( $settings->close_icon_size ) && ! empty( $settings->close_icon_size ) ? $settings->close_icon_size . 'px' : ''; ?>;
    height: <?php echo isset( $settings->close_icon_size ) && ! empty( $settings->close_icon_size ) ? $settings->close_icon_size . 'px' : ''; ?>;
}

<?php // Size - tablet ?>

<?php if ( isset( $settings->close_icon_size_medium ) && ! empty( $settings->close_icon_size_medium ) ) : ?>
    @media (max-width: <?php echo $global_settings->medium_breakpoint; ?>px) {
        .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active #<?php echo $cpt . '-' . $popup->ID; ?>__close-button {
            width: <?php echo $settings->close_icon_size_medium; ?>px;
            height: <?php echo $settings->close_icon_size_medium; ?>px;
        }
    }
<?php endif; ?>

<?php // Size - mobile ?>

<?php if ( isset( $settings->close_icon_size_responsive ) && ! empty( $settings->close_icon_size_responsive ) ) : ?>
    @media (max-width: <?php echo $global_settings->medium_breakpoint; ?>px) {
        .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active #<?php echo $cpt . '-' . $popup->ID; ?>__close-button {
            width: <?php echo $settings->close_icon_size_responsive; ?>px;
            height: <?php echo $settings->close_icon_size_responsive; ?>px;
        }
    }
<?php endif; ?>

<?php // Hide/show ?>

<?php if ( isset( $settings->modal_disable_close_icon ) && 'yes' === $settings->modal_disable_close_icon ) : ?>
    .single-<?php echo $cpt; ?>.<?php echo $cpt . '-' . $popup->ID; ?>--active #<?php echo $cpt . '-' . $popup->ID; ?>__close-button {
        display: none;
    }
<?php endif; ?>
