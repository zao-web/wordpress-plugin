<div class="misc-pub-section gc-item-name">
	<span class="dashicons dashicons-edit"></span> <?php echo esc_html_x( 'Item:', 'GatherContent item name', 'gathercontent-importer' ); ?> <# if ( data.item ) { #><a href="<?php $this->output( 'url' ); ?>item/{{ data.item }}" target="_blank"><# } #>{{ data.itemName }}<# if ( data.item ) { #></a><# } #>
</div>

<div class="misc-pub-section misc-gc-updated">
	<span class="dashicons dashicons-calendar"></span> <?php echo esc_html_x( 'Last Updated:', 'GatherContent updated date', 'gathercontent-importer' ); ?> <b class="<# if ( ! data.current ) { #>not-<# } #>current" title="<# if ( data.current ) { #><?php esc_attr_e( 'Your post is current.', 'gathercontent-importer' ); ?><# } else { #><?php esc_attr_e( 'Your post is behind.', 'gathercontent-importer' ); ?><# } #>">{{{ data.updated }}}</b>
</div>

<div class="misc-pub-section misc-pub-gc-mapping">
	<span class="dashicons dashicons-media-document"></span>

	<?php esc_html_e( 'Mapping Template:', 'gathercontent-importer' ); ?>
	<strong>
	<# if ( data.mappingLink ) { #>
	<a href="{{ data.mappingLink }}">
		<# if ( data.mappingStatus ) { #>
		{{ data.mappingStatus }}
		<# } else { #>
		{{ data.mappingName }}
		<# } #>
	</a>
	<# } else { #>
	{{ data.mappingName }}
	<# } #>
	</strong>
</div>

<div class="gc-major-publishing-actions">
	<div class="gc-publishing-action">
		<?php // $this->output( 'refresh_link' ); ?>
		<span class="spinner <# if ( data.mappingStatusId && data.mappingStatusId in { syncing : 1, starting: 1 } ) { #>is-active<# } #>"></span>
		<button id="gc-push" type="button" class="button gc-button-primary alignright" <# if ( ! data.mapping ) { #>disabled="disabled"<# } #>><?php esc_html_e( 'Push', 'gathercontent-importer' ); ?></button>
		<button id="gc-pull" type="button" class="button gc-button-primary alignright" <# if ( ! data.mapping || ! data.item ) { #>disabled="disabled"<# } #>><?php esc_html_e( 'Pull', 'gathercontent-importer' ); ?></button>
	</div>
	<div class="clear"></div>
</div>
<?php
	echo "<# console.log( 'data', data ); #>";