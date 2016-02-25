jQuery(document).ready(function() {

		if (jQuery('#wpjm-options-form').length > 0) {

			jQuery('table#wpjm-post-types-table tbody th, table#wpjm-post-types-table td').css('cursor','move');

			jQuery('#wpjm-post-types-table tbody').sortable({
				items: 'tr',
				cursor: 'move',
				axis: 'y',
				containment: 'table#wpjm-post-types-table',
				scrollSensitivity: 40
			});

			jQuery('input.colorPicker').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					jQuery(el).val(hex);
					jQuery(el).ColorPickerHide();
				},
				onBeforeShow: function() {
					jQuery(this).ColorPickerSetColor(this.value);
				},
				onChange: function(hsb, hex, rgb, el) {
					// console.log('hex change: '+hex);
					// console.log(el);
					var elRel = jQuery(el).attr('rel');
					// console.log(elRel);
					elRel = elRel.split('|');
					// console.log(elRel);
					jQuery( elRel[0] )
						.css( elRel[1], '#' + hex);
				}
			}).bind('keyup', function() {
				jQuery(this).ColorPickerSetColor(this.value);
				var elRel = jQuery(this).attr('rel');
				elRel = elRel.split('|');
				jQuery( elRel[0] ).css( elRel[1], '#' + jQuery(this).val() );
			});
			/*jQuery('#wpjm_backgroundColor').ColorPicker({
				onSubmit: function(hsb,hex,rgb,el) {
					jQuery(el).val(hex);
					jQuery(el).ColorPickerHide();
				},
				onBeforeShow: function() {
					jQuery(this).ColorPickerSetColor(this.value);
				},
				onChange: function (hsb, hex, rgb) {
					jQuery('#jump_menu').css('backgroundColor', '#' + hex);
				}
				}).bind('keyup',function() {
					jQuery(this).ColorPickerSetColor(this.value);
				});*/
		}



});
