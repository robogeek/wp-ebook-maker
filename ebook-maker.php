<?php
/*
  Plugin Name: eBook Maker
  Plugin URI: https://github.com/robogeek/wp-ebook-maker
  Description: Assist with constructing eBooks on a Wordpress website
  Version: 0.1.1
  Author: David Herron
  License: GPLv2 or later

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


add_action( 'init', 'ebookmaker_init' );

/**
 * Initialize the ebook_page post type and other initializaton
 */
function ebookmaker_init() {
    
    register_post_type( 'ebook_page',
    array(
        'labels' => array(
            'name' => __( 'Book Pages' ),
            'singular_name' => __( 'Book Page' ),
            'add_new'            => _x( 'Add New', 'book', 'your-plugin-textdomain' ),
            'add_new_item'       => __( 'Add New Book', 'your-plugin-textdomain' ),
            'new_item'           => __( 'New Book', 'your-plugin-textdomain' ),
            'edit_item'          => __( 'Edit Book', 'your-plugin-textdomain' ),
            'view_item'          => __( 'View Book', 'your-plugin-textdomain' ),
            'all_items'          => __( 'All Books', 'your-plugin-textdomain' ),
            'search_items'       => __( 'Search Books', 'your-plugin-textdomain' ),
            'parent_item_colon'  => __( 'Parent Books:', 'your-plugin-textdomain' ),
            'not_found'          => __( 'No books found.', 'your-plugin-textdomain' ),
            'not_found_in_trash' => __( 'No books found in Trash.', 'your-plugin-textdomain' )
        ),
        'public'         => true,
        'has_archive'    => true,
        'rewrite'        => array('slug' => 'ebooks', 'feeds' => true, 'with_front' => true),
        'hierarchical'   => true,
		'supports'       => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'page-attributes' ),
		'show_ui'        => true,
		'taxonomies'     => array( 'post_tag' )
    )
  );
    
}


function ebook_maker_show_prev_next($post) {
	
    $posttop = array_reverse(get_post_ancestors($post->ID))[0];
    
    if ($posttop) {
        $postID = $posttop;
        $post_parent = get_post($posttop);
        $post_title = $post_parent->post_title;
    } else {
        $postID = $post->ID;
        $post_title = $post->post_title;
    }
	
	// $posttop = ebook_maker_tree_top($post);
	// $postID = $posttop->ID;
	// $post_title = $posttop->post_title;
	// $post_parent = get_post($posttop);

	$pgs = get_pages(array(
	  'post_type' => 'ebook_page',
	  'sort_column' => 'menu_order',
	  'child_of' => $postID,							
	  'hierarchical' => 1,
	  ));

	// print_r($pgs);
	  
	for ($pgsindx = 0; $pgsindx < count($pgs); $pgsindx++) {
	  if ($pgs[$pgsindx]->ID == $post->ID) {
		  // echo 'FOUND '. $pgsindx .' '. $pgs[$pgsindx]->ID .' '. $pgs[$pgsindx]->post_title;
		  $pgcur = $pgs[$pgsindx];
		  // print_r($pgs[$pgsindx]);
		  
		  $pgsprevindx = $pgsindx - 1;
		  if ($pgsprevindx < 0) $pgsprevindx = count($pgs) - 1;
		  // echo 'PREV '. $pgsprevindx .' '. $pgs[$pgsprevindx]->ID .' '. $pgs[$pgsprevindx]->post_title;
		  $pgprev = $pgs[$pgsprevindx];
		  // print_r($pgs[$pgsprevindx]);
		  
		  
		  $pgsnextindx = $pgsindx + 1;
		  if ($pgsnextindx >= count($pgs)) $pgsnextindx = 0;
		  // echo 'NEXT '. $pgsnextindx .' '. $pgs[$pgsnextindx]->ID .' '. $pgs[$pgsnextindx]->post_title;
		  $pgnext = $pgs[$pgsnextindx];
		  // print_r($pgs[$pgsnextindx]);
		  
		  break;
	  }
	}
	
	if ($pgprev || $pgnext) {
		?><div id="nav-below" class="navigation"><?php
	}
	
	if ($pgprev) {
	  ?><div class="nav-previous"><a href="<?php
			  echo get_permalink($pgprev->ID);
	  ?>">&laquo; <?php echo $pgprev->post_title; ?></a></div><?php
	}
	
	/* if ($pgcur) {
	  ? ><p>CUR <a href="< ?php
			  echo get_permalink($pgcur->ID);
	  ? >">< ?php echo $pgcur->post_title; ? ></a></p>< ?php
	} */
	
	if ($pgnext) {
	  ?><div class="nav-next"><a href="<?php
			  echo get_permalink($pgnext->ID);
	  ?>"><?php echo $pgnext->post_title; ?> &raquo;</a></div><?php
	}
	
	if ($pgprev || $pgnext) {
		?></div><?php
	}
	
							  						  					  
}

/**
 * Template tag to display the full page hierarchy.
 */
function ebook_maker_show_book_children($post) {
    
    $posttopancestor = array_reverse(get_post_ancestors($post->ID))[0];
    
    if ($posttopancestor) {
        $postID = $posttopancestor;
        $post_parent = get_post($posttopancestor);
        $post_title = $post_parent->post_title;
    } else {
        $postID = $post->ID;
        $post_title = $post->post_title;
    }
    
    ?><ul>
    <ul>
    <li><a href="<?php echo get_permalink($postID); ?>"><?php echo $post_title; ?></a>
    <ul>
    <?php 
      wp_list_pages(array(
         'post_type' => 'ebook_page',
         'sort_column' => 'menu_order',
         'child_of' => $postID,
         'title_li' => null 
      ));
     ?>
    </ul></li></ul><?php
}

?>