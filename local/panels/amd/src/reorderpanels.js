define(
[
    'jquery',
    'core/notification',
    'core/custom_interaction_events',
    'local_panels/sortable_list'
],
function (
    $,
    Notification,
    CustomEvents,
    SortableList
) {

    var SELECTORS = {
        PANEL_ROW_CONTAINER: '[data-region="panel-row"]',
        PREVIEW_PANELS: '[data-region="previewpanels"]',
        PANEL_ORDER_FIELD: 'input[name="panelorder"]',
    };

    /**
     * Add events listeners for editing the zones and content
     * are read.
     *
     * @param {Object} root The root container element.
     */
    var registerEventListeners = function(root) {
        CustomEvents.define(root, [CustomEvents.events.activate]);

        var panelsContainer = root.find(SELECTORS.PREVIEW_PANELS);

        var getPanelName = function(element) {
            return element.find('h3.panelname').text();
        };

        var panelsSortable = new SortableList(panelsContainer,
            {moveHandlerSelector: '[data-action=movepanel] > [data-drag-type=move]'});
        panelsSortable.getElementName = function(element) {
            return $.Deferred().resolve(getPanelName(element));
        };

        var panels = root.find(SELECTORS.PANEL_ROW_CONTAINER);

        panels.on(SortableList.EVENTS.DROP, function(e, info) {
            e.stopPropagation();
            if (info.positionChanged) {
                var editgroups = root.find('div.panelroweditgroup');

                var order = [];
                editgroups.each(function() {
                    order.push($(this).attr('data-panelid'));
                });
                root.find(SELECTORS.PANEL_ORDER_FIELD).val(order);
            }
        });
    };

    /**
     * Initialize the edit Panels JS
     * @param  {Object} root root container element
     */
    var init = function(root) {
        root = $(root);
        registerEventListeners(root);
    };

    return {
        init: init
    };
});