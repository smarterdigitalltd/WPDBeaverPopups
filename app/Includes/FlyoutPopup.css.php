<?php if ( isset( $settings->popup_type ) && 'fly_out' === $settings->popup_type ) : ?>
    <?php // Builder and single popup styles ?>

    .single-<?php echo $cpt; ?>.<?php echo $cpt; ?>__fly_out--active #<?php echo $cpt . '-' . $popup->ID; ?>__outer {
        position: fixed;

        <?php // X position ?>

        <?php if ( isset( $settings->fly_out_x_position ) ) {
            if ( 'center' === $settings->fly_out_x_position ) : ?>
                left: 50%;
                right: auto;
                -webkit-transform: translateX(-50%);
                -moz-transform: translateX(-50%);
                -ms-transform: translateX(-50%);
                -o-transform: translateX(-50%);
                transform: translateX(-50%);
            <?php else : $x_direction = $settings->fly_out_x_position; ?>
                <?php echo $x_direction; ?>: 0;
            <?php endif; ?>
        <?php } ?>

        <?php // Y position ?>

        <?php if ( isset( $settings->fly_out_y_position ) ) :
            if ( $y_direction = $settings->fly_out_y_position ) : ?>
                <?php echo $y_direction; ?>: 0;
            <?php endif; ?>
        <?php endif; ?>
    }

    <?php // WP Admin bar overrides ?>

    <?php if ( isset( $settings->fly_out_y_position ) && 'top' === $settings->fly_out_y_position ) : ?>
        .admin-bar:not(.fl-builder-edit) #<?php echo $cpt . '-' . $popup->ID; ?>,
        .single-<?php echo $cpt; ?>.admin-bar #<?php echo $cpt . '-' . $popup->ID; ?>__outer {
            margin-top: 32px;
        }
    <?php endif; ?>

    <?php // Builder overrides ?>

    .fl-builder-edit.<?php echo $cpt; ?>__fly_out--active #<?php echo $cpt . '-' . $popup->ID; ?>__outer {
        transition: all 0.3s linear;

        <?php if ( isset( $settings->fly_out_y_position ) && 'top' === $settings->fly_out_y_position ) : ?>
            margin-top: 43px;
        <?php endif; ?>
    }
<?php endif; ?>
