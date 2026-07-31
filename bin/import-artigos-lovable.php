<?php
/**
 * Importa artigos e receitas do preview Lovable para posts WordPress.
 *
 * Uso: C:\xampp\php\php.exe bin/import-artigos-lovable.php
 *
 * @package ValleBrancoProdutos
 */

require 'C:/xampp/htdocs/valle-branco/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

define( 'VB_LOVABLE_ASSETS', 'C:/Users/eliss/Desktop/V4 Company/02. SITES/VALLE BRANCO/site-valle-branco/src/assets/' );
define( 'VB_ARTIGOS_IMAGENS', __DIR__ . '/artigos-imagens/' );
/**
 * Dados exportados de site-valle-branco/src/data/artigos.ts
 *
 * @return array<int, array<string, mixed>>
 */
function vb_lovable_artigos() {
	$json = __DIR__ . '/artigos-lovable.json';
	if ( ! file_exists( $json ) ) {
		fwrite( STDERR, "Arquivo ausente: {$json}\n" );
		exit( 1 );
	}

	$data = json_decode( file_get_contents( $json ), true );
	if ( ! is_array( $data ) ) {
		fwrite( STDERR, "JSON inválido em artigos-lovable.json\n" );
		exit( 1 );
	}

	return $data;
}

/**
 * Garante categoria.
 *
 * @param string $name Nome.
 * @param string $slug Slug.
 * @return int
 */
function vb_ensure_category( $name, $slug ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	if ( $term && ! is_wp_error( $term ) ) {
		return (int) $term->term_id;
	}

	$result = wp_insert_term(
		$name,
		'category',
		array(
			'slug' => $slug,
		)
	);
	if ( is_wp_error( $result ) ) {
		if ( 'term_exists' === $result->get_error_code() ) {
			return (int) $result->get_error_data();
		}
		fwrite( STDERR, 'Erro categoria ' . $name . ': ' . $result->get_error_message() . "\n" );
		exit( 1 );
	}

	return (int) $result['term_id'];
}

/**
 * Sobe imagem da biblioteca Lovable.
 *
 * @param string $filename Nome do arquivo.
 * @return int Attachment ID.
 */
function vb_upload_lovable_image( $filename ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
			'meta_key'       => '_vb_lovable_asset',
			'meta_value'     => $filename,
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	$path = VB_ARTIGOS_IMAGENS . $filename;
	if ( ! file_exists( $path ) ) {
		$path = VB_LOVABLE_ASSETS . $filename;
	}
	if ( ! file_exists( $path ) ) {
		fwrite( STDERR, "Imagem ausente: {$filename}\n" );
		exit( 1 );
	}

	$tmp = wp_tempnam( $filename );
	copy( $path, $tmp );

	$att_id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		),
		0,
		pathinfo( $filename, PATHINFO_FILENAME )
	);

	if ( is_wp_error( $att_id ) ) {
		fwrite( STDERR, 'Erro upload ' . $filename . ': ' . $att_id->get_error_message() . "\n" );
		exit( 1 );
	}

	update_post_meta( $att_id, '_vb_lovable_asset', $filename );

	return (int) $att_id;
}

/**
 * Monta HTML do post.
 *
 * @param array<string, mixed> $artigo Artigo.
 * @return string
 */
function vb_build_artigo_content( array $artigo ) {
	$html  = '<div class="vb-artigo-conteudo">';
	$html .= '<p class="vb-artigo-resumo">' . esc_html( (string) $artigo['resumo'] ) . '</p>';

	if ( ! empty( $artigo['pontosChave'] ) && is_array( $artigo['pontosChave'] ) ) {
		$html .= '<section class="vb-artigo-pontos-chave"><h2>Destaques</h2><ul>';
		foreach ( $artigo['pontosChave'] as $ponto ) {
			$html .= '<li>' . esc_html( (string) $ponto ) . '</li>';
		}
		$html .= '</ul></section>';
	}

	foreach ( (array) ( $artigo['secoes'] ?? array() ) as $secao ) {
		$html .= '<section id="' . esc_attr( (string) $secao['id'] ) . '" class="vb-artigo-secao">';
		$html .= '<h2>' . esc_html( (string) $secao['titulo'] ) . '</h2>';

		foreach ( (array) ( $secao['paragrafos'] ?? array() ) as $paragrafo ) {
			$html .= '<p>' . esc_html( (string) $paragrafo ) . '</p>';
		}

		if ( ! empty( $secao['itens'] ) ) {
			$html .= '<ul>';
			foreach ( $secao['itens'] as $item ) {
				$html .= '<li>' . esc_html( (string) $item ) . '</li>';
			}
			$html .= '</ul>';
		}

		if ( ! empty( $secao['passos'] ) ) {
			$html .= '<ol>';
			foreach ( $secao['passos'] as $passo ) {
				$html .= '<li>' . esc_html( (string) $passo ) . '</li>';
			}
			$html .= '</ol>';
		}

		$html .= '</section>';
	}

	if ( ! empty( $artigo['faq'] ) ) {
		$html .= '<section id="faq" class="vb-artigo-faq"><h2>Perguntas frequentes</h2>';
		foreach ( $artigo['faq'] as $item ) {
			$html .= '<h3>' . esc_html( (string) $item['pergunta'] ) . '</h3>';
			$html .= '<p>' . esc_html( (string) $item['resposta'] ) . '</p>';
		}
		$html .= '</section>';
	}

	$html .= '</div>';

	return $html;
}

/**
 * Localiza post importado pelo slug Lovable.
 *
 * @param string $slug Slug.
 * @return int
 */
function vb_find_imported_post( $slug ) {
	$by_meta = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'meta_key'       => '_vb_lovable_slug',
			'meta_value'     => $slug,
			'fields'         => 'ids',
		)
	);
	if ( $by_meta ) {
		return (int) $by_meta[0];
	}

	$by_name = get_page_by_path( $slug, OBJECT, 'post' );
	return $by_name ? (int) $by_name->ID : 0;
}

$cat_receita = vb_ensure_category( 'Receita', 'receita' );
$cat_artigo  = vb_ensure_category( 'Artigo', 'artigo' );

$images = array();
foreach ( vb_lovable_artigos() as $artigo_img ) {
	$key = (string) $artigo_img['imageKey'];
	if ( $key && ! isset( $images[ $key ] ) ) {
		$images[ $key ] = vb_upload_lovable_image( $key );
	}
}

echo "Imagens na mídia:\n";
foreach ( $images as $file => $id ) {
	echo "  {$file} → #{$id}\n";
}

$imported = 0;
$updated  = 0;

foreach ( vb_lovable_artigos() as $artigo ) {
	$slug       = (string) $artigo['slug'];
	$image_key  = (string) $artigo['imageKey'];
	$thumb_id   = $images[ $image_key ] ?? 0;
	$category   = 'Receita' === $artigo['tag'] ? $cat_receita : $cat_artigo;
	$post_id    = vb_find_imported_post( $slug );
	$postarr    = array(
		'post_title'   => (string) $artigo['title'],
		'post_name'    => $slug,
		'post_excerpt' => (string) $artigo['desc'],
		'post_content' => vb_build_artigo_content( $artigo ),
		'post_status'  => 'publish',
		'post_type'    => 'post',
		'post_date'    => (string) $artigo['publishedAt'] . ' 09:00:00',
		'post_category'=> array( $category ),
	);

	if ( $post_id ) {
		$postarr['ID'] = $post_id;
		$result        = wp_update_post( $postarr, true );
		$updated++;
	} else {
		$result = wp_insert_post( $postarr, true );
		$imported++;
	}

	if ( is_wp_error( $result ) ) {
		fwrite( STDERR, "Erro post {$slug}: " . $result->get_error_message() . "\n" );
		continue;
	}

	$post_id = (int) $result;
	update_post_meta( $post_id, '_vb_lovable_slug', $slug );
	update_post_meta( $post_id, '_vb_reading_time', (string) $artigo['readingTime'] );
	update_post_meta( $post_id, '_vb_resumo', (string) $artigo['resumo'] );
	update_post_meta( $post_id, '_vb_artigo_tag', (string) $artigo['tag'] );

	if ( $thumb_id ) {
		set_post_thumbnail( $post_id, $thumb_id );
	}

	echo ( $postarr['ID'] ?? 'NOVO' ) . " → #{$post_id} | {$slug} | {$artigo['title']}\n";
}

echo "\nConcluído: {$imported} novos, {$updated} atualizados.\n";
