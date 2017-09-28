define(['jquery'], function ($) {

    var tabs = {};

    function SearchTabLoader (elem) {
        this.contentTab = $(elem);
        this.id = this.contentTab.attr('id');
        this.loadUri = this.contentTab.data('load');


        var selector = '.tab-container ul a[data-tab="#' + $(this).attr('id') + '"]';
        this.tab = $(selector);
        this.preloaded = this.contentTab.data().hasOwnProperty('preloaded');

        this.page = this.contentTab.data('page') || 1;
        this.perpage = this.contentTab.data('perpage') || 10;
        this.sort = this.contentTab.data('default-sort') || '-weight';
        this.region = this.contentTab.data('default-region') || 0;

        this.totalResults = this.contentTab.find('[data-result-count]').addBack('[data-result-count]').data('resultCount');

        if (!this.preloaded) {
            this.contentTab.hide(); // assume data-load content appears hidden at first
        }
    }

    SearchTabLoader.prototype.load = function (optionsOverride) {

        var options = $.extend({
            append: false,
            loader: false
        }, optionsOverride);

        var content = this.contentTab;
        var count   = this.tab.find('.count');
        var params  = {
            search: $('#localcoursesearch input[name="search"]').val(),
            page: this.page,
            perpage: this.perpage,
            sort: this.sort,
            region: this.region
        };


        var loaderClass = (options.append) ? 'loading-append' : 'loading-replace';
        content.removeClass('loading-append loading-replace').addClass('loading ' + loaderClass);


        var promise = $.Deferred();

        var me = this;

        $.get(
            this.loadUri,
            params,
            function (data) {

                // Set count on tab
                me.totalResults = $(data).find('[data-result-count]').addBack('[data-result-count]').data('resultCount');
                count.text(me.totalResults);

                // Add content to page
                if (options.append) {
                    // Get content from returned HTML
                    var newResults = $(data).find(options.append);
                    content.find(options.append).last().after(newResults);
                } else {
                    content.html(data);
                }
                // remove loading class
                setTimeout(function () {
                    content.removeClass('loading loading-append loading-replace');
                }, 0);

                promise.resolve(data);
            }
        );

        return promise;
    };

    SearchTabLoader.prototype.setPage = function (page) {
        this.page = page;
        return this; // allow chaining
    };
    SearchTabLoader.prototype.setSort = function (sort) {
        this.sort = sort;
        return this; // allow chaining
    };
    SearchTabLoader.prototype.setRegion = function (region) {
        this.region = region;
        return this; // allow chaining
    };

    SearchTabLoader.prototype.focus = function ()
    {
        this.tab.parent().addClass('active').siblings().removeClass('active');

        this.contentTab.addClass('open').show()
            .siblings().removeClass('open').hide();

        $(document).trigger($.Event('tabchanged', {tab: this}));
    }

    /* Helper to locate the right search tab. *note* static */
    SearchTabLoader.findSearchTab = function (elem) {

        if ($(elem).parents('.nav-tabs').length) {
            var elem = $(elem).data('tab');
        }

        var tabId = $(elem).closest('.tab-content').attr('id');

        return tabs[tabId];
    };

    SearchTabLoader.create = function (elem) {
        var tab   = new SearchTabLoader(elem);

        tabs[tab.id] = tab;

        return tab;
    };

    // Generate loaders
    $('.tab-content[data-load]').each(function () {
        var tab = SearchTabLoader.create(this);
        if (!tab.preloaded) {
            tab.load();
        }
    });

    $('.tab-container').on('click', 'ul a', function (e) {
        e.preventDefault();

        var searchTab = SearchTabLoader.findSearchTab(this);

        searchTab.focus();
    });

    // Check for anchor to show correct initial tab
    if (window.location.hash.length) {
        $('.tab-container ul a[data-tab="' + window.location.hash + '"]').trigger('click');
    }

    return SearchTabLoader;
});