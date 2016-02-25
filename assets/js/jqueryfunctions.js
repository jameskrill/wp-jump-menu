var WPJM_PARENT_ID = '#wp-admin-bar-wp-jump-menu';
var CACHE_KEY = 'wpjm_entries';

function wpjm_get_opts() {
    return jQuery(WPJM_PARENT_ID).data('opts');
}

function wpjm_render(html) {
    var opts = wpjm_get_opts();
    var $parent = jQuery(WPJM_PARENT_ID);
    $parent.append(html);
    var $el = jQuery('#wp-pdd').on('change', function () {
        if (this.value === '__reload__') {
            wpjm_load();
        } else {
            window.location = this.value;
        }
    });
    if (window.localStorage) {
        var $clearCacheOpt = jQuery('<option value="__reload__">' + opts.reloadText + '</option>');
        $el.find('option:last').parent().append($clearCacheOpt);
    }
    if (opts.useChosen) {
        $el.customChosen({position: opts.position, search_contains: true});
    }
}

function wpjm_load() {
    // remove old stuff if it's there
    jQuery(WPJM_PARENT_ID).children('*:not(script):not(.ab-item)').remove();
    // load new
    jQuery.get(wpjm_get_opts().baseUrl + '?action=wpjm_menu', function (html) {
        if (window.localStorage) {
            localStorage.setItem(CACHE_KEY, html);
        }
        wpjm_render(html);
    });
}

wpjm_init_html = function (opts) {
    var $parent = jQuery(WPJM_PARENT_ID);
    $parent.data('opts', opts);

    var cached = window.localStorage && window.localStorage.getItem(CACHE_KEY);
    if (cached) {
        wpjm_render(cached);
    }
    $parent.find('.ab-item').click(wpjm_load);
};

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
