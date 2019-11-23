CMB2 Post Search field
======================

Custom field for CMB2 which adds a post-search dialog for searching/attaching other post IDs.

Adds a new text field type (with a button), `post_search_text` that adds a quick post search dialog for saving post IDs to a text input.

## Example

```php
// Classic CMB2 declaration
$cmb = new_cmb2_box( array(
	'id'           => 'prefix-metabox-id',
	'title'        => __( 'Post Info' ),
	'object_types' => array( 'post', ), // Post type
) );

// Add new field
$cmb->add_field( array(
	'name'        => __( 'Related post' ),
	'id'          => 'prefix_related_post',
	'type'        => 'post_search_text', // This field type
	// post type also as array
	'post_type'   => 'post',
	// Default is 'checkbox', used in the modal view to select the post type
	'select_type' => 'radio',
	// Will replace any selection with selection from modal. Default is 'add'
	'select_behavior' => 'replace',
) );

// Add new field with Post Type Filter
$cmb->add_field( array(
	'name'        => __( 'Related post' ),
	'id'          => 'prefix_related_post',
	'type'        => 'post_search_text', // This field type
	// post type should be an array
	'post_type'   => [ 'post', 'page' ],
	//filter shown only if post type is an array and its size greater than or equal to two
	'post_type_filter' => true,
	// Default is 'checkbox', used in the modal view to select the post type
	'select_type' => 'radio',
	// Will replace any selection with selection from modal. Default is 'add'
	'select_behavior' => 'replace',
) );

```

## Screenshots

1. Field display  
![Field display](https://raw.githubusercontent.com/nandakrishnancse/CMB2-Post-Search-field-with-post-type-filter/master/post-search-field.png)

2. Search Modal  
![Search Modal](https://raw.githubusercontent.com/nandakrishnancse/CMB2-Post-Search-field-with-post-type-filter/master/post-search-dialog.png)

3. Search Modal with post type filter  
![Search Modal](https://raw.githubusercontent.com/nandakrishnancse/CMB2-Post-Search-field-with-post-type-filter/master/post-search-filter-dialog.png)

----
```
Courtacy repository : https://github.com/CMB2/CMB2-Post-Search-field
```
