<?php
require 'C:/xampp/htdocs/valle-branco/wp-load.php';
$ts = get_terms( array( 'taxonomy' => 'vb_categoria_produto', 'hide_empty' => false ) );
foreach ( $ts as $t ) {
	echo $t->term_id . "\t" . $t->name . "\t" . $t->slug . "\t" . $t->count . PHP_EOL;
}
