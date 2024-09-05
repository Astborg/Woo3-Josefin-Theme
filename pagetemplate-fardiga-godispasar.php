<?php
/*
Template Name: Färdiga Godispåsar
*/

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        

        <form method="get" id="filter-form">
        <h1>Färdiga Godispåsar</h1>
            <label for="filter_title">Titel:</label>
            <input type="text" name="filter_title" id="filter_title" value="<?php echo isset($_GET['filter_title']) ? esc_attr($_GET['filter_title']) : ''; ?>">

            <label for="filter_date">Datum (YYYY-MM-DD):</label>
            <input type="date" name="filter_date" id="filter_date" value="<?php echo isset($_GET['filter_date']) ? esc_attr($_GET['filter_date']) : ''; ?>">

            <label for="filter_category">Kategori:</label>
            <?php
            $categories = get_terms(array(
                'taxonomy' => 'category', // Använd din taxonomi här
                'hide_empty' => false,
            ));

            if (!empty($categories)) {
                echo '<select name="filter_category" id="filter_category">';
                echo '<option value="">Alla kategorier</option>';
                foreach ($categories as $category) {
                    echo '<option value="' . esc_attr($category->slug) . '" ' . selected($_GET['filter_category'], $category->slug, false) . '>' . esc_html($category->name) . '</option>';
                }
                echo '</select>';
            }
            ?>

            <button type="submit">Filtrera</button>
        </form>

        <?php
        // Förbered query-argumenten baserat på användarens input
        $args = array(
            'post_type' => 'godispase',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        // Lägg till filter för titel
        if (!empty($_GET['filter_title'])) {
            $args['s'] = sanitize_text_field($_GET['filter_title']);
        }

        // Lägg till filter för datum
        if (!empty($_GET['filter_date'])) {
            $args['date_query'] = array(
                array(
                    'year'  => date('Y', strtotime($_GET['filter_date'])),
                    'month' => date('m', strtotime($_GET['filter_date'])),
                    'day'   => date('d', strtotime($_GET['filter_date'])),
                ),
            );
        }

        // Lägg till filter för kategori
        if (!empty($_GET['filter_category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'category', // Se till att du använder rätt taxonomi här
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['filter_category']),
                ),
            );
        }

        $godispasar = new WP_Query($args);

        if ($godispasar->have_posts()) {
            echo '<ul>';
            while ($godispasar->have_posts()) {
                $godispasar->the_post();
                ?>
              <li>
    <h2><?php the_title(); ?></h2>
    <div><strong>Skapad:</strong> <?php echo get_the_date(); ?></div>
    <div><?php the_content(); ?></div>
    
    <?php 
    // Hämta och visa kategorierna
    $terms = get_the_terms(get_the_ID(), 'category');
    if ($terms && !is_wp_error($terms)) {
        $categories = array();
        foreach ($terms as $term) {
            $categories[] = $term->name;
        }
        echo '<div><strong>Kategori:</strong> ' . implode(', ', $categories) . '</div>';
    }

    // Hämta relaterade produkter
    $related_products = get_post_meta(get_the_ID(), '_related_products', true);
    
    // Visa köp-länken om det finns relaterade produkter
    if (!empty($related_products)) : ?>
        <a href="<?php echo esc_url(add_query_arg('add_godispase_to_cart', get_the_ID())); ?>" class="button">Köp denna godispåse</a>
    <?php endif; ?>
</li>
                <?php
            }
            echo '</ul>';
        } else {
            echo '<p>Inga färdiga godispåsar matchar ditt filter.</p>';
        }
        wp_reset_postdata();
        ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>