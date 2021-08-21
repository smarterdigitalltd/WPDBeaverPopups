<?php

use WPD\BeaverPopups\Helpers\BeaverBuilderHelper;
use WPD\BeaverPopups\Plugin;

$cpt      = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ];
$settings = BeaverBuilderHelper::getPopupStyleSettings( null, get_the_ID() ); ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="<?php echo $cpt . '-' . get_the_ID(); ?>__outer" class="<?php echo $cpt . '__outer'; ?>">
    <div id="<?php echo $cpt . '-' . get_the_ID(); ?>__content" class="<?php echo $cpt . '__content'; ?>">
        <div id="<?php echo $cpt . '-' . get_the_ID(); ?>__inner" class="<?php echo $cpt; ?>__inner">
            <div id="<?php echo $cpt . '-' . get_the_ID(); ?>__close-button">
                <svg viewBox="0 0 24 24">
                    <path d="M22.2,4c0,0,0.5,0.6,0,1.1l-6.8,6.8l6.9,6.9c0.5,0.5,0,1.1,0,1.1L20,22.3c0,0-0.6,0.5-1.1,0L12,15.4l-6.9,6.9c-0.5,0.5-1.1,0-1.1,0L1.7,20c0,0-0.5-0.6,0-1.1L8.6,12L1.7,5.1C1.2,4.6,1.7,4,1.7,4L4,1.7c0,0,0.6-0.5,1.1,0L12,8.5l6.8-6.8c0.5-0.5,1.1,0,1.1,0L22.2,4z"></path>
                </svg>
            </div>

			<?php if ( method_exists( 'FLBuilder', 'render_content_by_id' ) ) :
				\FLBuilder::render_content_by_id( get_the_ID() );
			else :
				if ( have_posts() ) : while ( have_posts() ) : the_post();
					the_content();
				endwhile; endif;
			endif; ?>
        </div>
    </div>
</div>
</body>

<?php wp_footer(); ?>
</html>
