(function () {

    $('.section .section-tab').find('a').each(function(){
        var cUrl = String(window.location).split('?')[0];
        if (cUrl.substr(cUrl.length - 1) == '#') {
          cUrl = cUrl.slice(0,-1);
        }
        if ($($(this))[0].href.split('?')[0]==cUrl) {
            $('.section .section-tab').removeClass('selected')
            $(this).parent().addClass('selected')
            $('.accordion-container').removeClass('selected')
            $(this).closest('div[class^="accordion-container"]').addClass('selected')
            $('.tab-focus').addClass('selected')
        }
    })
    var selectd = $('.sections>.selected:first');
    var title = selectd.find('.accordion-title .span-title').text();
    $('.account-info .title .span-title').text(title);
    var items = selectd.find('.accordion-content .accordion-item');
    $('#content-top-bar').empty();
    $('#content-top-bar').append($(`<div class="tab-container-background"></div>`));
    // $('.section .section-tab').removeClass('selected')
    items.each((i, e) => {
        var app = $(`<div class="tab-item section-tab"></div>`)
            .append($($(e).html()))
        if ($(e).hasClass('selected')) {
            app.addClass('selected');
        }
        $('#content-top-bar').append(app)
    })
    $('#sidebar-toggle-icon').on('click', function () {
        $('.sidebar').toggleClass('collapsed')
    })
    $('.sections .section').on('click', function () {
        $('.sections .section').removeClass('selected')
        $(this).addClass('selected')
    })
    $('.is-expandable').on('click', function () {
        $(this).toggleClass('is-expanded')
    })

    function resetSidebarToggle() {
        if (window.innerWidth < 1126) {
            $('.sidebar').addClass('collapsed');
        } else {
            $('.sidebar').removeClass('collapsed');
        }
    }
    resetSidebarToggle();
    $(window).resize(function () {
        resetSidebarToggle();
    });

    $('.clear.icon-icon-clear').on('click', function () {
        $(this).siblings('input').val('');
    });

    // table   
    $('.lt-head .lt-column.sort').on('click', function () {
        var sortValue = $(this).attr('data-sort-value');
        $(this).attr('data-sort-value', {
            'none': 'down',
            'down': 'up',
            'up': 'none',
        } [sortValue]);
        if (search) search();
    });
    if ($('.resize-table').length > 0) {
        var resizeTableList = [];
        $('.resize-table').each(function (i, e) {
            resizeTableList.push((function (e) {
                var width = undefined;
                return function () {
                    // resize
                    var changeWidth = 120;
                    var toRender = false;
                    if (width === undefined || width > $(e).width()) {
                        // to small
                        while (true) {
                            var th = $(e).find('th:not(.resize-table-hide)');
                            if (th.length <= 2) {
                                break;
                            }
                            var n = $(e).find('th:not(.resize-table-hide)').length;
                            if ($(e).find('th:not(.resize-table-hide):nth-child(' + n + ')').width() >= changeWidth) {
                                break;
                            }
                            $(e).find('th:first-child,td:first-child').show();
                            $(e).find('th:not(.resize-table-hide):nth-child(' + n + ')').addClass('resize-table-hide');
                            $(e).find('tr:not(.expend) td:not(.resize-table-hide):nth-child(' + n + ')').addClass('resize-table-hide');
                            $(e).find('tr.expend td').attr('colspan', n - 1);
                            toRender = true;
                        }
                    }
                    if (width === undefined || width < $(e).width()) {
                        // to big
                        while (true) {
                            var th = $(e).find('th:not(.resize-table-hide)');
                            if ($(th[0]).width() + (th.length + 1) * changeWidth > $(e).width()) {
                                break;
                            }
                            var n = $(e).find('th.resize-table-hide').length;
                            if (n === 0) {
                                $(e).find('th:first-child,td:first-child').hide();
                                break;
                            }
                            $(e).find('th:nth-last-child(' + n + ')').removeClass('resize-table-hide');
                            $(e).find('tr:not(.expend) td:nth-last-child(' + n + ')').removeClass('resize-table-hide');
                            $(e).find('tr.expend td').attr('colspan', th.length + 1);
                            toRender = true;
                        }
                    }
                    if (width === undefined || toRender) {
                        if ($(e).find('th.resize-table-hide').length > 0) {
                            $(e).find('th:first-child,td:first-child').show();
                        } else {
                            $(e).find('th:first-child,td:first-child').hide();
                        }
                        renderTableExpendContent(e);
                    }
                    width = $(e).width();
                }
            })(e));
        });

        function renderTableExpendContent(table) {
            var label = [];
            $(table).find('th.resize-table-hide').each(function (i, e) {
                label.push($(e).text().trim());
            });
            $(table).find('tr.expend').each((i, trExpend) => {
                $(trExpend).find('dl').empty();
                $(trExpend).prev().find('td.resize-table-hide').each(function (i, e) {
                    $(trExpend).find('dl').append($('<dt>' + (label[i] ? label[i] + ':' : '') + '</dt>')).append(
                        '<dd>' + $(e).html() + '</dd>'
                    );
                });
            });
        }
        setInterval(function () {
            resizeTableList.forEach(function (f) {
                f();
            })
        }, 300);
        $('.resize-table td:first-child').on('click', function () {
            if ($(this).find('i').hasClass('icon-icon-arrow-right')) {
                $(this).find('i').attr('class', 'fa icon-icon-arrow-down');
                $(this).parents('tr')
                    .after($('<tr class="lt-row expend"><td class="lt-cell align-left" colspan="' +
                        $(this).parents('.resize-table').find('th:not(.resize-table-hide)').length +
                        '"><div class="row"><dl class="dl-horizontal" style="margin:1em 0;"></dl></div></td></tr>'));
                renderTableExpendContent($(this).parents('.resize-table'));
            } else {
                $(this).find('i').attr('class', 'fa icon-icon-arrow-right');
                $(this).parents('tr').next().remove();
            }
        });
    }
})();