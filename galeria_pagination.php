<?php 

//Modifica a galeria nativa
add_filter( 'post_gallery', 'gallery_custon', 10, 2 );
function gallery_custon( $output, $attr) {

		global $post, $wp_locale;

		static $instance = 0;
		$instance++;

		if ( isset( $attr['orderby'] ) ) {
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
			if ( !$attr['orderby'] )
				unset( $attr['orderby'] );
		}

		extract(shortcode_atts(array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post->ID,
			'itemtag'    => 'figure',
			'icontag'    => 'li',
			'captiontag' => 'figurecaption',
			'columns'    => 3,
			'size'       => 'thumbnail',
			'include'    => '',
			'exclude'    => ''
		), $attr));

		$id = intval($id);
		if ( 'RAND' == $order )
			$orderby = 'none';

		if ( !empty($include) ) {
			$include = preg_replace( '/[^0-9,]+/', '', $include );
			$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif ( !empty($exclude) ) {
			$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
			$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		} else {
			$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		}

		if ( empty($attachments) )
			return '';

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment )
				$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
			return $output;
		}
		$path_theme = get_bloginfo('template_url');
		$itemtag = tag_escape($itemtag);
		$captiontag = tag_escape($captiontag);
		$columns = intval($columns);
		$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
		$float = is_rtl() ? 'right' : 'left';

		$selector = "gallery-{$instance}";

		ob_start();
		?>
		<style>
			li.gridder-list {
			    float: left;
			    margin-left: 15px;
			    list-style: none;
			    margin-bottom: 15px;
			}
		</style>
		<ul class="pgwSlider">
			<?php 
				$limit = 9;
				$max_pages = ceil(count($attachments)/$limit);
				$page = isset($_GET['pg-galeria']) ? $_GET['pg-galeria'] : 1;

				$offset = ($page - 1) * $limit  + 1;
				$top_display = $limit * $page;
				$c = 1;
				foreach ( $attachments as $id => $attachment ):
				if ( $captiontag && trim($attachment->post_excerpt) ) {
					$legenda = wptexturize($attachment->post_excerpt);
				} else {
					$legenda = '';
				}
				$full = isset($attr['link']) && $attr['link']=='file' ? wp_get_attachment_image_src($id, 'fullsize', false, false) : wp_get_attachment_image_src($id, 'fullsize', true, false);	
				$thumb = isset($attr['link']) && $attr['link']=='file' ? wp_get_attachment_image_src($id, 'thumbnail', false, false) : wp_get_attachment_image_src($id, 'medium', true, false);	
			?>
				<li class="gridder-list" style="<?php echo $c >= $offset && $c <=$top_display ? 'display:initial' : 'display:none' ?>">
					<a href='<?php echo $full[0] ?>' title='<?php echo $legenda ?>'>
						<img src='<?php echo $thumb[0] ?>' />
					</a>
				</li>
			<?php $c++; endforeach; ?>
		</ul>
		<?php
		$output = ob_get_contents();
		ob_clean();	
		return $output;
}