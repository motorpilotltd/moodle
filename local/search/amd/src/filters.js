define(['jquery', 'local_search/searchtabs'], function ($) {

    var selector;
    var $filterblock;

    $('.tab-container').on('click', 'button.toggle-filters', function (event) {
        event.stopPropagation();
        $filterblock.toggle();
    });
    $(document).on('click', function (event) {

        var $clickedElem;

        if ($filterblock.is(':visible')) {

            $clickedElem = event.target;

            if ($clickedElem.closest(selector)) {
                return;
            }

            $filterblock.toggle();
        }
    });

    $('.filter-block-container form').on('submit', function () {
        // Grab our region id and add to hidden field in filters form
        $filterblock.find('input[name="region"]').val($('#course-region-select').val());
        return true;
    });

    return {
        init: function () {
            selector = '.filter-block-container';
            $filterblock = $(selector);
            $filterblock.removeClass('hide'); // this class is only added to ensure block is not visible before JS kicks in
            $filterblock.hide();
        }
    }
});
