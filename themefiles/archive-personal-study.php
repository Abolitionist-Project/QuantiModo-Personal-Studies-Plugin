<?php

get_header();

if(have_posts()) : while(have_posts()) : the_post();

    the_title();

    echo '<div class="entry-content">';

    the_content();

    echo '</div>';

endwhile; endif;

get_footer();

?>
