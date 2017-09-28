define(['jquery', 'local_search/searchtabs'], function ($, tabLoader) {

    function InfiniteScrollList()
    {
        this.active = true;
        this.loading = false;

        this.container = null;
        this.resultsSelector = null;
        this.tab = null;

        var me = this;

        this.handleScroll = function () {
            if (me.loading) {
                return false;
            }

            me.activateScrollable();

            if (me.atBottom()) {
                me.loadMore();
            }
        };

        $(document).on('scroll', this.handleScroll);

        // reset our loading status when tabs are changed
        $(document).on('tabchanged', function () {
            me.loading = false;
        });

    }

    InfiniteScrollList.prototype.activateScrollable = function ()
    {
        var activeTab = $('.tab-container .tab-content.open');
        this.container = $(activeTab.data('resultsContainer'));
        this.resultsSelector = activeTab.data('resultElement');
        this.tab = tabLoader.findSearchTab(this.container);
    }

    InfiniteScrollList.prototype.atBottom = function ()
    {
        var elem = this.container.find(this.resultsSelector).last();
        var container = $(window);
        var elemTop = elem.offset().top;
        var scrollBottom = container.scrollTop() + container.height();

        return scrollBottom > elemTop;
    };

    InfiniteScrollList.prototype.hasMore = function ()
    {
        var totalResults = this.tab.totalResults;
        var totalResultsDisplayed = this.tab.contentTab.find(this.resultsSelector).length;

        return (totalResults > totalResultsDisplayed);
    };

    InfiniteScrollList.prototype.loadMore = function ()
    {
        var me = this;

        if (false === this.hasMore()) {
            return false;
        }

        this.loading = true;

        this.tab.setPage(this.tab.page + 1)
            .load({ append: this.resultsSelector, loader: true})
            .done(function (data) {
                me.loaded(data);
            });
    };

    InfiniteScrollList.prototype.loaded = function (data)
    {
        this.loading = false;

        this.tab.contentTab.find('.description').collapser({
            mode: 'lines',
            truncate: 3,
            hideText: 'Show less',
            speed: 'medium'
        });
    };

    return {
        apply: function () {
            new InfiniteScrollList();
        }
    };
});