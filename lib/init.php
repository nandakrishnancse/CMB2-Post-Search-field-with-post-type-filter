<?php
class CMB2_Post_Search_field_with_filters {
	protected static $single_instance = null;
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}
		return self::$single_instance;
	}
	protected function __construct() {
		add_action( 'cmb2_render_post_search_text', array( $this, 'render_field' ), 10, 5 );
		add_action( 'cmb2_after_form', array( $this, 'render_js' ), 10, 4 );
		add_action( 'cmb2_post_search_field_add_find_posts_div', array( $this, 'add_find_posts_div' ) );
		add_action( 'admin_init', array( $this, 'ajax_find_posts' ) );
	}
	public function render_field( $field, $escaped_value, $object_id, $object_type, $field_type ) {
		echo $field_type->input( array(
			'data-search' => json_encode( array(
				'posttype'   => $field->args( 'post_type' ),
				'posttypefilter' => ((bool)$field->args( 'post_type_filter' ) && is_array($field->args( 'post_type' )) && sizeof($field->args( 'post_type' )) >=1) ? true : false,
				'selecttype' => 'radio' == $field->args( 'select_type' ) ? 'radio' : 'checkbox',
				'selectbehavior' => 'replace' == $field->args( 'select_behavior' ) ? 'replace' : 'add',
				'errortxt'   => esc_attr( $field_type->_text( 'error_text', __( 'An error has occurred. Please reload the page and try again.' ) ) ),
				'findtxt'    => esc_attr( $field_type->_text( 'find_text', __( 'Find Posts or Pages' ) ) ),
			) ),
		) );
	}
	public function render_js(  $cmb_id, $object_id, $object_type, $cmb ) {
		static $rendered;
		if ( $rendered ) {
			return;
		}
		$fields = $cmb->prop( 'fields' );
		if ( ! is_array( $fields ) ) {
			return;
		}
		$has_post_search_field = $this->has_post_search_text_field( $fields );
		if ( ! $has_post_search_field ) {
			return;
		}
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wp-backbone' );
		if ( ! is_admin() ) {
			require_once( ABSPATH . 'wp-admin/includes/template.php' );
			do_action( 'cmb2_post_search_field_add_find_posts_div' );
		}
		add_action( 'admin_footer', 'find_posts_div' );
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			'use strict';
			var SearchView = window.Backbone.View.extend({
				el         : '#find-posts',
				overlaySet : false,
				$overlay   : false,
				$idInput   : false,
				$checked   : false,
				events : {
					'keypress .find-box-search :input' : 'maybeStartSearch',
					'change #find-posts-post-type-filter-select' : 'send',
					'keyup #find-posts-input'  : 'escClose',
					'click #find-posts-submit' : 'selectPost',
					'click #find-posts-search' : 'send',
					'click #find-posts-close'  : 'close',
				},
				initialize: function() {
					this.$spinner  = this.$el.find( '.find-box-search .spinner' );
					this.$input    = this.$el.find( '#find-posts-input' );
					this.$response = this.$el.find( '#find-posts-response' );
					this.$overlay  = $( '.ui-find-overlay' );
					this.listenTo( this, 'open', this.open );
					this.listenTo( this, 'close', this.close );
				},
				escClose: function( evt ) {
					if ( evt.which && 27 === evt.which ) {
						this.close();
					}
				},
			close: function() {
					this.$overlay.hide();
					this.$el.hide();
				},
				open: function() {
					var search = this;
					var $selecttypefilterhtml = '';
					this.$response.html('');
					this.$el.show().find( '#find-posts-head' ).html( this.findtxt + '<div id="find-posts-close"></div>' );
					this.$el.show().find( '#find-posts-post-type-filter' ).remove();
					if ( search.posttypefilter ) {
						$selecttypefilterhtml += '<label>Filter By Post Type :</label><select id="find-posts-post-type-filter-select">';
						$selecttypefilterhtml += '<option value="' + search.posttype + '">' + search.posttype + '</option>';
						if( $.isArray( search.posttype ) && search.posttype.length >=2 ) {
							$.each( search.posttype, function( option_index, option ) {
							    $selecttypefilterhtml += '<option>' + option + '</option>';
							});
						}
						$selecttypefilterhtml += '</select>';
						var $post_type_filter_html = '<div id="find-posts-post-type-filter">' + $selecttypefilterhtml + '</div>';
						$( $post_type_filter_html ).insertBefore( this.$el.show().find( '.find-box-search .clear' ) );
					} 
					this.$input.focus();
					if ( ! this.$overlay.length ) {
						$( 'body' ).append( '<div class="ui-find-overlay"></div>' );
						this.$overlay  = $( '.ui-find-overlay' );
					}
					this.$overlay.show();
					this.send();
					return false;
				},
				maybeStartSearch: function( evt ) {
					if ( 13 == evt.which ) {
						this.send();
						return false;
					}
				},
				send: function() {
					var search = this;
					search.$spinner.addClass('is-active');
					if( search.posttypefilter ) {
						var posttypeseleted = $( '#find-posts-post-type-filter-select' ).val().split( ',' );
					} else {
						var posttypeseleted = search.posttype;
					}
					$.ajax( ajaxurl, {
						type     : 'POST',
						dataType : 'json',
						data     : {
							ps               : search.$input.val(),
							action           : 'find_posts',
							cmb2_post_search : true,
							post_type_filter : search.posttypefilter,
							post_search_cpt  : posttypeseleted,
							_ajax_nonce      : $('#find-posts #_ajax_nonce').val()
						}
					}).always( function() {

						search.$spinner.removeClass('is-active');
					}).done( function( response ) {
						if ( ! response.success ) {
							search.$response.text( search.errortxt );
						}
						var data = response.data;
						if ( 'checkbox' === search.selecttype ) {
							data = data.replace( /type="radio"/gi, 'type="checkbox"' );
						}
						search.$response.html( data );
					}).fail( function() {
						search.$response.text( search.errortxt );
					});
				},
				selectPost: function( evt ) {
					evt.preventDefault();
					this.$checked = $( '#find-posts-response input[type="' + this.selecttype + '"]:checked' );
					var checked = this.$checked.map(function() { return this.value; }).get();
					if ( ! checked.length ) {
						this.close();
						return;
					}
					this.handleSelected( checked );
				},
				handleSelected: function( checked ) {
					checked = checked.join( ', ' );
					if ( 'add' === this.selectbehavior ) {
						var existing = this.$idInput.val();
						if ( existing ) {
							checked = existing + ', ' + checked;
						}
					}
					this.$idInput.val( checked ).trigger( 'change' );
					this.close();
				}
			});
			window.cmb2_post_search = new SearchView();
			window.cmb2_post_search.closeSearch = function() {
				window.cmb2_post_search.trigger( 'close' );
			};
			window.cmb2_post_search.openSearch = function( evt ) {
				var search = window.cmb2_post_search;
				search.$idInput = $( evt.currentTarget ).parents( '.cmb-type-post-search-text' ).find( '.cmb-td input[type="text"]' );
				$.extend( search, search.$idInput.data( 'search' ) );
				search.trigger( 'open' );
			};
			window.cmb2_post_search.addSearchButtons = function() {
				var $this = $( this );
				var data = $this.data( 'search' );
				$this.after( '<div title="'+ data.findtxt +'" class="dashicons dashicons-search cmb2-post-search-button"></div>');
			};
			$( '.cmb-type-post-search-text .cmb-td input[type="text"]' ).each( window.cmb2_post_search.addSearchButtons );
			$( '.cmb2-wrap' ).on( 'click', '.cmb-type-post-search-text .cmb-td .dashicons-search', window.cmb2_post_search.openSearch );
			$( 'body' ).on( 'click', '.ui-find-overlay', window.cmb2_post_search.closeSearch );
		});
		</script>
		<style type="text/css" media="screen">
			.cmb2-post-search-button {
				color: #999;
				margin: .3em 0 0 2px;
				cursor: pointer;
			}
			#find-posts-post-type-filter {
				float: right;
			}
			#find-posts-post-type-filter label{
				font-weight: bolder;
				padding: 3px;
			}
			#find-posts-post-type-filter-select {
				text-transform:capitalize;	
			}

		</style>
		<?php
		$rendered = true;
	}
	public function has_post_search_text_field( $fields ) {
		foreach ( $fields as $field ) {
			if ( isset( $field['fields'] ) ) {
				if ( $this->has_post_search_text_field( $field['fields'] ) ) {
					return true;
				}
			}
			if ( 'post_search_text' == $field['type'] ) {
				return true;
			}
		}
		return false;
	}
	public function add_find_posts_div() {
		add_action( 'wp_footer', 'find_posts_div' );
	}
	public function ajax_find_posts() {
		if (
			defined( 'DOING_AJAX' )
			&& DOING_AJAX
			&& isset( $_POST['cmb2_post_search'], $_POST['action'], $_POST['post_search_cpt'] )
			&& 'find_posts' == $_POST['action']
			&& ! empty( $_POST['post_search_cpt'] )
		) {
			add_action( 'pre_get_posts', array( $this, 'set_post_type' ) );
		}
	}
	public function set_post_type( $query ) {
		$types = $_POST['post_search_cpt'];
		$types = is_array( $types ) ? array_map( 'esc_attr', $types ) : esc_attr( $types );
		$query->set( 'post_type', $types );
	}
}
CMB2_Post_Search_field_with_filters::get_instance();
if ( ! function_exists( 'cmb2_post_search_render_field' ) ) {
	function cmb2_post_search_render_field( $field, $escaped_value, $object_id, $object_type, $field_type ) {
		return CMB2_Post_Search_field_with_filters::get_instance()->render_field( $field, $escaped_value, $object_id, $object_type, $field_type );
	}
}
remove_action( 'cmb2_render_post_search_text', 'cmb2_post_search_render_field', 10, 5 );
remove_action( 'cmb2_after_form', 'cmb2_post_search_render_js', 10, 4 );
if ( ! function_exists( 'cmb2_has_post_search_text_field' ) ) {
	function cmb2_has_post_search_text_field( $fields ) {
		return CMB2_Post_Search_field_with_filters::get_instance()->has_post_search_text_field( $fields );
	}
}
