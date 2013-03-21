<?php
//Modifica a galeria nativa

add_filter( 'post_gallery', 'galeria_dica', 10, 2 );
function galeria_dica( $output, $attr) {
	if(in_category('teste')){
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
			'itemtag'    => 'ul',
			'icontag'    => 'li',
			'captiontag' => 'dd',
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

		$itemtag = tag_escape($itemtag);
		$captiontag = tag_escape($captiontag);
		$columns = intval($columns);
		$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
		$float = is_rtl() ? 'right' : 'left';

		$selector = "dica-gallery-{$instance}";

		$output = apply_filters('gallery_style', "
			<style type='text/css'>
				#{$selector} {
					margin: auto;
					position:relative;
				}
				#{$selector} .gallery-item {
					float: {$float};
					margin-top: 10px;
					text-align: center;
					width: {$itemwidth}%; }
				#{$selector} img {
					border: 2px solid #cfcfcf;
				}
				#{$selector} .gallery-caption {
					margin-left: 0;
				}
				.box-image-{$instance}{position:relative; margin:0 auto}
				#img-destacada-{$instance}{margin:auto; width:100%; text-align:center; position:relative}
				.legenda-{$instance}{background:#000; opacity:0.75;position:absolute;bottom:4px; height:40px; left:0; width:100%}
				.legenda-{$instance} p{color:#fff; text-align:center;  line-height: 40px;}
			</style>
			<script type=\"text/javascript\">
				jQuery(document).ready(function(){
					jQuery('#{$selector} a').first().click();
				});
				function loadImgDiv_{$instance}(img,legenda,widthImg){
					if(legenda !=0){
						var legenda = '<div class=\"legenda-{$instance}\"><p>'+legenda+'</p></div>';
					}
					var boxImage = '<div class=\"box-image-{$instance}\" style=\"width:'+widthImg+'px\"><img src='+img+' />'+legenda+'</div>';				
					jQuery('#img-destacada-{$instance}').html(boxImage);										
				}
				jQuery(function() {
					jQuery('.{$selector} ').jCarouselLite({
							btnNext: '.next-{$instance}',
							btnPrev: '.prev-{$instance}',
							visible: 3,
							circular: false,
							scroll: 3
						});
					});
			</script>
			<div id='img-destacada-{$instance}'></div>
			<div id='$selector' class='galleryid-{$id} {$selector} '>");
			$output .= "<button class=\"prev-{$instance}\"><<</button>
						<button class=\"next-{$instance}\">>></button>";
			$output .= "<ul class='gallery-item'>";
			$i = 0;
				foreach ( $attachments as $id => $attachment ) {			
					$thumb = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_image_src($id, $size, false, false) : wp_get_attachment_image_src($id, $size, true, false);
					$imgFull = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_image_src($id, 'fullsize', false, false) : wp_get_attachment_image_src($id, 'fullsize', true, false);
					if ( $captiontag && trim($attachment->post_excerpt) ) {
							$legenda = wptexturize($attachment->post_excerpt);
					}
					
					$output .= "<li class='gallery-icon'>
									<a href='javascript:void(0);' onclick=\"loadImgDiv_{$instance}('{$imgFull[0]}','{$legenda}','{$imgFull[1]}');\" title='{$legenda}'><img src='{$thumb[0]}' /></a>
								</li>";
					
					//if ( $columns > 0 && ++$i % $columns == 0 )
						//$output .= '<br style="clear: both" />';	
				}
			$output .= "</ul>";
			$output .= "
			<br style='clear: both;' />	
			</div>\n";

		return $output;
	}
}
add_action('wp_enqueue_scripts', 'jcarousellite');
function jcarousellite(){
     if(in_category('teste'))
            wp_enqueue_script('jcarousellite_dica', get_template_directory_uri().'/js/jcarousellite_1.0.1.min.js', array('jquery'),1,true);
}
