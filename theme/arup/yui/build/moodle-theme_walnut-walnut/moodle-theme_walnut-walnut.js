YUI.add('moodle-theme_arup-arup', function (Y, NAME) {

/* arup.js
 * copyright  @copyright  2015 basbrands.nl
 * author     Bas Brands
 * license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *  */

var togglesidebar = function() {
    var sidebaropen = Y.one('body').hasClass('sidebaropen');
    var hassidepre = Y.one('body').hasClass('used-region-side-pre');
    var hassidepost = Y.one('body').hasClass('used-region-side-post');
    var editing = Y.one('body').hasClass('editing');

    if (!editing) {
        if (sidebaropen) {
            if (hassidepre && hassidepost) {
                var content = 'col-sm-9 col-lg-10';
                var pre = 'col-sm-0';
            } else if (hassidepre && sidebaropen) {
                var content =  'col-sm-12';
                var pre = 'col-sm-0';
            }
        } else {
            if (hassidepre && hassidepost) {
                var content = 'col-sm-6 col-sm-push-3 col-lg-8 col-lg-push-2';
                var pre = 'col-sm-3 col-sm-pull-6 col-lg-2 col-lg-pull-8';
            } else if (hassidepre) {
                var content =  'col-sm-9 col-sm-push-3 col-lg-10 col-lg-push-2';
                var pre = 'col-sm-3 col-sm-pull-9 col-lg-2 col-lg-pull-10';
            }
        }

        Y.one('#region-main').setAttribute('class', content);
        Y.one('#block-region-side-pre').setAttribute('class', pre);

        if (sidebaropen) {
            Y.one('body').removeClass('sidebaropen');
            M.util.set_user_preference('theme_arup_sidebar', '');
        } else {
            Y.one('body').addClass('sidebaropen');
            M.util.set_user_preference('theme_arup_sidebar', 'sidebaropen');
        }
        this.toggleClass('morph-menu-active');
    }
};

//When the button with class .moodlezoom is clicked fire the onZoom function
M.theme_arup = M.theme_arup || {};
M.theme_arup.arup =  {
  init: function() {
    console.log('Initializing arup JS');
    Y.one('body').delegate('click', togglesidebar, '#menu-trigger');
  }
};

}, '@VERSION@', {"requires": ["node", "io-form", "json-parse"]});
