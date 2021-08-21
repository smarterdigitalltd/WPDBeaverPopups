<?php // Popup border radius?>

<?php if (isset($settings->border_radius) && ! empty($settings->border_radius)) : ?>
    body:not(.single-<?php echo $cpt; ?>) #<?php echo $cpt . '-' . $popup->ID; ?> .jBox-content,
    .single-<?php echo $cpt; ?> #<?php echo $cpt . '-' . $popup->ID; ?>__content .fl-builder-content {
       overflow: auto;
       overflow-x: hidden;
       border-radius: <?php echo $settings->border_radius; ?>px;
    }

    .fl-builder-edit #<?php echo $cpt . '-' . $popup->ID; ?>__content .<?php echo $cpt . '__inner'; ?> {
        position: relative;
    }

    .fl-builder-edit #<?php echo $cpt . '-' . $popup->ID; ?>__content .fl-row {
        position: initial !important;
    }
<?php endif; ?>