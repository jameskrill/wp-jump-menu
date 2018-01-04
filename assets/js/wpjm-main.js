/**
 * Pseudo Class WPJM
 *
 * Contains functions to setup and initialize the jump menu.
 *
 * @returns {WPJM}
 * @constructor
 */
var WPJM = function () {

  var WPJM_PARENT_ID = '#wp-admin-bar-wp-jump-menu',
    CACHE_KEY = 'wpjm_entries',
    shift_on = false,
    self = this;

  /**
   * wpjm_get_opts()
   *
   * Get options from data attribute on parent element
   */
  this.wpjm_get_opts = function () {
    return jQuery(WPJM_PARENT_ID).data('opts');
  };

  /**
   * wpjm_render()
   *
   * Uses html var to:
   * - hide the loader
   * - append the html
   * - add event listeners
   * - append the "Refresh Jump Menu" option to the select menu
   * - init chosen plugin on select
   *
   * @param html
   */
  this.wpjm_render = function (html) {
    var opts = self.wpjm_get_opts();
    var $parent = jQuery(WPJM_PARENT_ID);
    $parent.find('.loader').hide();
    $parent.append(html);
    var $el = jQuery('#wp-pdd').on('change', function () {
      if (this.value === '__reload__') {
        self.wpjm_refresh();
      } else {
        if (self.shift_on === true) {
          $selected = jQuery(this).find('option').eq(this.selectedIndex);
          window.location = $selected.data('permalink');
        } else {
          window.location = this.value;
        }
      }
    });

    var $clearCacheOpt = jQuery('<option value="__reload__">' + opts.reloadText + '</option>');
    $el.find('option:last').parent().append($clearCacheOpt);

    if (opts.useChosen) {
      $el.customChosen({position: opts.position, search_contains: true});
      if (opts.currentPageID) {
        var $option = $el.find('[data-post-id=' + opts.currentPageID + ']');
        $option.prop("selected", true);
        $el.trigger('chosen:updated');
      }
    }

    // Add event listener for Control + J if it is activated
    if (opts.useShortcut) {
      jQuery(document).on('keydown', null, 'ctrl+j', function () {
        $el.trigger('chosen:open');
      });
    }
  };

  /**
   * wpjm_load()
   *
   * Ajax call to load the menu
   *
   * Gets the data options from the parent element
   * Removes the menu if it is present
   * Ajax request to get select menu, then calls wpjm_render() to inject it
   */
  this.wpjm_load = function () {

    var wpjm_opts_cache = self.wpjm_get_opts();

    if (wpjm_opts_cache != undefined) {
      // remove old stuff if it's there
      jQuery(WPJM_PARENT_ID).children('*:not(script):not(.ab-item, .loader)').remove();
      // load new, ajax call
      jQuery.get(self.wpjm_get_opts().baseUrl + '?action=wpjm_menu&post_id=' + self.wpjm_get_opts().currentPageID, function (html) {
        self.wpjm_render(html);
      });
    }

  };

  /**
   * wpjm_refresh()
   *
   * Similar to wpjm_load, except it contains "refresh=true" to clear cache...?
   */
  this.wpjm_refresh = function () {
    // remove old stuff if it's there
    jQuery(WPJM_PARENT_ID).children('*:not(script):not(.ab-item, .loader)').remove();
    // load new
    jQuery.get(self.wpjm_get_opts().baseUrl + '?action=wpjm_menu&refresh=true&post_id=' + self.wpjm_get_opts().currentPageID, function (html) {
      self.wpjm_render(html);
    });
  };

  /**
   * wpjm_init_html()
   *
   * Initializes the menu
   *
   * calls wpjm_load to load the menu and then inject it
   * calls wpjm_key_watcher - which binds the shift key for loading on front-end
   *
   * @param opts
   */
  this.wpjm_init_html = function (opts) {
    var $parent = jQuery(WPJM_PARENT_ID);
    $parent.data('opts', opts);

    self.wpjm_load();
    self.wpjm_key_watcher();

    $parent.find('.ab-item').click(self.wpjm_refresh);
  };

  /**
   * wpjm_key_watcher()
   *
   * Add event listener for when a menu option is clicked with the shift key held down
   * sets class variable "shift_on" to true while shift is held down
   */
  this.wpjm_key_watcher = function () {
    window.onkeydown = function (e) {
      if (!e) e = window.event;
      if (e.shiftKey) {
        self.shift_on = true;
      }
    };
    window.onkeyup = function (e) {
      self.shift_on = false;
    };
  };

  return this;

};


// Set global variable
var wpjm = new WPJM;

// On document ready, init wpjm
jQuery(document).ready(function () {

  // wpjm_opt comes from localized script variable
  wpjm.wpjm_init_html({
    baseUrl:       wpjm_opt.baseUrl,
    useChosen:     wpjm_opt.useChosen,
    position:      wpjm_opt.position,
    reloadText:    wpjm_opt.reloadText,
    currentPageID: wpjm_opt.currentPageID,
    useShortcut:   wpjm_opt.useShortcut,
    isAdmin: wpjm_opt.isAdmin
  });

});
