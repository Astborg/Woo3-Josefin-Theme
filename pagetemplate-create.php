<?php
/*
Template Name: Create Godispåse
*/

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        

        <form method="post" action="" id="create-godispase-form">
        <h1>Skapa din egen godispåse</h1>
        <label for="pase_namn">Namnge din godispåse:</label>
        <input type="text" name="pase_namn" id="pase_namn" required>

            <label for="godisar">Välj godisar:</label>
            <select name="godisar[]" id="godisar" multiple>
                <?php
                // Hämta alla godisar (använd gärna en custom post type för godis om det finns)
                $args = array('post_type' => 'product', 'posts_per_page' => -1);
                $products = new WP_Query($args);

                if ($products->have_posts()) {
                    while ($products->have_posts()) {
                        $products->the_post();
                        ?>
                        <option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
                        <?php
                    }
                } else {
                    echo '<option>Inga godisar tillgängliga</option>';
                }
                wp_reset_postdata();
                ?>
            </select>

    <label for="kategori">Välj kategori:</label>
    <select name="kategori" id="kategori" required>
        <?php
        $categories = get_categories(array(
            'taxonomy' => 'category',
            'hide_empty' => false,
        ));

        if (!empty($categories)) {
            foreach ($categories as $category) {
                echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
            }
        }
        ?>
    </select>
            <input type="submit" name="submit_godispase" value="Skapa godispåse">
        </form>

        <?php
        // Hantera formulärinlämning
        if (isset($_POST['submit_godispase'])) {
            $selected_godisar = $_POST['godisar'];
            $pase_namn = sanitize_text_field($_POST['pase_namn']);
            $kategori = intval($_POST['kategori']);

            if (!empty($selected_godisar)&& !empty($pase_namn)) {
                echo '<h2>Din godispåse innehåller:</h2><ul>';
                foreach ($selected_godisar as $godis_id) {
                    echo '<li>' . get_the_title($godis_id) . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>Inga godisar valda.</p>';
            }
        }
        if (isset($_POST['submit_godispase'])) {
            $selected_godisar = $_POST['godisar'];
            if (!empty($selected_godisar)) {
                $godispase_content = '';
                foreach ($selected_godisar as $godis_id) {
                    $godispase_content .= get_the_title($godis_id) . "\n";
                }
        
                // Skapa ny post av typen godispåse
                $new_post = array(
                    'post_title' => $pase_namn, // Du kan anpassa detta eller låta användaren namnge påsen
                    'post_content' => $godispase_content,
                    'post_status' => 'publish',
                    'post_type' => 'godispase',
                    'post_category' => array($kategori),
                );
        
                $post_id = wp_insert_post($new_post);

                if ($post_id) {
                    // Spara de valda produkterna (godisarna) som relaterade produkter i godispåsen
                    $related_products = array_map('intval', $selected_godisar); // Array med godis-ID:n
                    update_post_meta($post_id, '_related_products', $related_products);

                    // Skapa en ny WooCommerce-produkt för godispåsen
                    $product = new WC_Product_Simple();
                    $product->set_name($pase_namn);
                    $product->set_regular_price(99); // Sätt ett standardpris eller anpassa detta
                    $product->set_description($godispase_content);
                    $product->set_status('publish');
                    $product_id = $product->save();

                    // Koppla produkt-ID till godispåsen
                    update_post_meta($post_id, '_related_product_id', $product_id);

                    echo '<p>Din godispåse "' . esc_html($pase_namn) . '" har skapats och är nu tillgänglig för köp!</p>';
                    
                } else {
                    echo '<p>Det gick inte att skapa din godispåse.</p>';
                }
            } else {
                echo '<p>Inga godisar valda eller inget namn angivet.</p>';
            }
        }
        ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer();