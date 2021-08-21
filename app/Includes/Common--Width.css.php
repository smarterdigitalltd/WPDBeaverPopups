<?php // Popup width ?>

.single-<?php echo $cpt; ?> #<?php echo $cpt . '-' . $popup->ID; ?>__content .fl-builder-content {
    <?php if ( isset( $settings->is_full_width ) && 'true' === $settings->is_full_width ) : ?>
        width: 100vw;
    <?php elseif ( isset( $settings->width ) && ! empty( $settings->width ) ) : ?>
        max-width: 100%;
        width: <?php echo $settings->width; ?>px;
    <?php endif; ?>
}
