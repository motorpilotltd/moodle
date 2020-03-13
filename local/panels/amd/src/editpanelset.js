define(
[
    'jquery',
    'core/custom_interaction_events'
],
function(
    $,
    CustomEvents
) {

    var SELECTORS = {
        ZONE_CONTAINER: '[data-region="zone"]',
        PANEL_ROW_CONTAINER: '[data-region="panel-row"]',
        PREVEW_PANELS: '[data-region="previewpanels"]',
        COLLAPSE_ACTIONS: '.collapsible-actions',
        EDIT_PANEL: '[data-action="editpanel"]',
        DELETE_PANEL: '[data-action="deletepanel"]',
        EDIT_HEADING: '[data-region="currentform"]',
        FIELDSETS: 'fieldset.collapsible',
        DATASOURCEMODESELECT: '.datasourcemodeselector select',
        DATASOURCETYPESELECT: '.datasourcetypeselector select'
    };

    /**
     * Highlight the active zone.
     * @param  {Object} root root container element
     * @param  {Object} zone zone to hightlight
     */
    var highLightZone = function(root, element, allzones) {
        var highlightablezones = SELECTORS.ZONE_CONTAINER + ', ' + SELECTORS.PANEL_ROW_CONTAINER + ', ' + SELECTORS.EDIT_PANEL;
        root.find(highlightablezones).each(function(index, otherzone) {
            $(otherzone).removeClass('border-warning').removeClass('bg-gray010');
        });
        if (allzones) {
            element.closest(SELECTORS.PANEL_ROW_CONTAINER).find(highlightablezones).each(function(index, otherzone) {
                $(otherzone).addClass('border-warning').addClass('bg-gray010');
            });
        } else {
            element.addClass('border-warning').addClass('bg-gray010');
        }
    };
    /**
     * Add events listeners for editing the zones and content
     * are read.
     *
     * @param {Object} root The root container element.
     */
    var registerEventListeners = function(root) {
        CustomEvents.define(root, [CustomEvents.events.activate]);

        var datasourcetypeselect = function(node) {
            var panelid = node.data('panelid');
            var zoneid = node.data('zoneid');
            var datasourcetype = node.val();

            $("span.datasourceconfig").toggleClass('d-none', true);
            $("span.datasourceconfig.panel-" + panelid + "-zone-" + zoneid + "-" + datasourcetype).toggleClass('d-none', false);
        };

        var datasourcemodeselect = function(node) {
            var panelid = node.data('panelid');
            var sourceperzone = node.val() == 10;

            var selector = 'fieldset.collapsible#id_panel-' + panelid + '-zone-0';
            $(selector).toggleClass('collapsed', sourceperzone).toggleClass('d-none', sourceperzone);

            if (!sourceperzone) {
                datasourcetypeselect($(selector).find('.datasourcetypeselector select'));
            }
        };

        root.on(CustomEvents.events.activate, SELECTORS.ZONE_CONTAINER + ',' + SELECTORS.EDIT_PANEL, function(e, data) {
            var element = $(e.target).closest(SELECTORS.ZONE_CONTAINER + ',' + SELECTORS.EDIT_PANEL);
            var zoneid = element.attr('data-zone');
            var panelrow = element.closest(SELECTORS.PANEL_ROW_CONTAINER);
            var panelid = panelrow.attr('data-panelid');

            // Are we configuring a data source per zone, or one for the whole panel.
            var sourceperzone = $('select[name="panel-' + panelid + '-datasourcemode"]').val() == 10;

            // Show the relevant form.
            $('fieldset.collapsible').toggleClass('collapsed', true);

            var selector = '#id_panel-' + panelid;
            if (zoneid !== undefined && sourceperzone) {
                selector += '-zone-' + zoneid;
            }
            if (!sourceperzone) {
                selector += ', ' + selector + '-zone-0';
            }

            var formsection = root.find(selector);
            var heading = root.find(SELECTORS.EDIT_HEADING);

            var headinglabel = 'Panel ' + panelid;

            if (zoneid !== undefined && sourceperzone) {
                headinglabel += ' Zone ' + zoneid;
            }

            heading.html(headinglabel);

            formsection.removeClass('d-none').removeClass('collapsed');
            var mode = formsection.find(SELECTORS.DATASOURCEMODESELECT);
            var type = formsection.find(SELECTORS.DATASOURCETYPESELECT);
            datasourcemodeselect(mode);
            datasourcetypeselect(type);

            $(selector).toggleClass('collapsed', false);

            $('[data-region="submitcancel"]').removeClass('d-none').addClass('d-flex');

            highLightZone(root, element, !sourceperzone);

            data.originalEvent.preventDefault();
        });

        root.on(CustomEvents.events.activate, SELECTORS.DELETE_PANEL, function(e) {
            var element = $(e.target).closest(SELECTORS.DELETE_PANEL);
            var panelrow = element.closest(SELECTORS.PANEL_ROW_CONTAINER);
            var panelid = panelrow.attr('data-panelid');


            var selector = 'fieldset[id^="id_panel-' + panelid + '"], [data-panelid="' + panelid + '"]';

            root.find(selector).remove();
        });

        root.on('change', SELECTORS.DATASOURCEMODESELECT, function(e) {
            var element = $(e.target).closest(SELECTORS.DATASOURCEMODESELECT);
            datasourcemodeselect(element);

            var sourceperzone = element.val() == 10;
            var panelid = element.attr('data-panelid');
            var settingsbutton = $(SELECTORS.PANEL_ROW_CONTAINER + '[data-panelid="' + panelid + '"]' + ' ' + SELECTORS.EDIT_PANEL);
            highLightZone(root, settingsbutton, !sourceperzone);
        });

        root.on('change', SELECTORS.DATASOURCETYPESELECT, function(e) {
            var element = $(e.target).closest(SELECTORS.DATASOURCETYPESELECT);
            datasourcetypeselect(element);
        });
    };

    /**
     * Initialize the edit Panels JS
     * @param  {Object} root root container element
     */
    var init = function(root) {
        root = $(root);
        root.find(SELECTORS.COLLAPSE_ACTIONS).addClass('d-none');
        registerEventListeners(root);
    };

    return {
        init: init
    };
});