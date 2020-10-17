<?php
/*
*
Template Name: Full-Width
*/
get_header(); ?>
<div id="primary" class="content-area">

    <main id="main" class="site-main" role="main">

        <?php

        // Start the loop.

        while ( have_posts() ) : the_post();
        	?>

            

            <div class="entry-content">

                        <?php the_content(); ?>

            </div>



           <?php 

        endwhile;

        ?>

    </main><!-- .site-main -->

</div><!-- .content-area -->

<?php

get_footer(); ?>