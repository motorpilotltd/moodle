define(['jquery',
        'local_search/searchtabs',
        'local_search/infinitescroll',
        'local_search/videoframes',
        'local_search/collapser',
        'local_search/filters'
        ],
        function ($, tabLoader, scroller, videoframes, collapser, filters) {

            var submitSearchForm = function ()
            {
                var form = $(this).parents('form');
                var regionSelect = $('#course-region-select');
                var regionInput = form.find('input[name="region"]');

                // Inject the selected region
                if (regionInput.length) {
                    regionInput.val(regionSelect.val());
                } else {
                    form.append('<input name="region" value="' + regionSelect.val() + '" type="hidden">');
                }

                // Potentially need to inject filters

                form.submit();
            };

            var truncateLines = function (tab)
            {
                tab.contentTab.find('.description').collapser({
                    mode: 'lines',
                    truncate: 3,
                    hideText: 'Show less',
                    speed: 'medium'
                });
            };

            $(document).on('tabchanged', function (ev) {
                // The truncate only works when a tab is open, so we do it here just in case
                truncateLines(ev.tab);
            });

            return {
                init: function () {

                    filters.init();
                    scroller.apply();
                    videoframes.apply();
                    collapser.extend();

                    $('#localcoursesearch').on('click', 'button', submitSearchForm);

                    // Send new request for updated sort order
                    $('.tab-container').on('change', '.orderby-form select', function () {
                        var sort = $(this).val();
                        var tab = tabLoader.findSearchTab(this).setPage(0).setSort(sort);

                            tab.load().done(function () {
                                truncateLines(tab);
                            });
                    });

                    // Send new request for updated sort order
                    $('.tab-container').on('change', '.region-filter-form select', function () {
                        var region = $(this).val();
                        var startPage = 0;
                        var tab = tabLoader.findSearchTab(this)

                        // hack for courses, which need to start at page 1
                        if (tab.contentTab.find('.course-results')) {
                            startPage = 1;
                        }

                        tab.setPage(startPage).setRegion(region);

                        tab.load().done(function () {
                            truncateLines(tab);
                        });
                    });
                }
            };
        }
);