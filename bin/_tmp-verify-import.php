<?php
require 'C:/xampp/htdocs/valle-branco/wp-load.php';
echo 'page_for_posts=' . get_option('page_for_posts') . PHP_EOL;
$cats = get_categories(['hide_empty'=>false]);
foreach ($cats as $c) {
    echo 'CAT ' . $c->slug . ' (' . $c->count . ') ' . $c->name . PHP_EOL;
}
$posts = get_posts(['post_type'=>'post','posts_per_page'=>-1,'post_status'=>'publish','orderby'=>'date','order'=>'DESC']);
echo 'POSTS ' . count($posts) . PHP_EOL;
foreach ($posts as $p) {
    $thumb = get_post_thumbnail_id($p->ID);
    $terms = wp_get_post_terms($p->ID, 'category', ['fields'=>'names']);
    $rt = get_post_meta($p->ID, '_vb_reading_time', true);
    echo $p->ID . ' | ' . implode(',', $terms) . ' | ' . $rt . ' | thumb#' . $thumb . ' | ' . $p->post_name . PHP_EOL;
}
