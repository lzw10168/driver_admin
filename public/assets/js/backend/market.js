define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'market/index' + location.search,
                    add_url: 'market/add',
                    edit_url: 'market/edit',
                    del_url: 'market/del',
                    multi_url: 'market/multi',
                    table: 'market',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'spot_limit_buy', title: __('Spot_limit_buy'), searchList: {"1":__('Spot_limit_buy 1'),"2":__('Spot_limit_buy 2')}, formatter: Table.api.formatter.normal},
                        {field: 'spot_limit_sell', title: __('Spot_limit_sell'), searchList: {"1":__('Spot_limit_sell 1'),"2":__('Spot_limit_sell 2')}, formatter: Table.api.formatter.normal},
                        {field: 'spot_market_buy', title: __('Spot_market_buy'), searchList: {"1":__('Spot_market_buy 1'),"2":__('Spot_market_buy 2')}, formatter: Table.api.formatter.normal},
                        {field: 'spot_market_sell', title: __('Spot_market_sell'), searchList: {"1":__('Spot_market_sell 1'),"2":__('Spot_market_sell 2')}, formatter: Table.api.formatter.normal},
                        {field: 'swap_limit_buy', title: __('Swap_limit_buy'), searchList: {"1":__('Swap_limit_buy 1'),"2":__('Swap_limit_buy 2')}, formatter: Table.api.formatter.normal},
                        {field: 'swap_limit_sell', title: __('Swap_limit_sell'), searchList: {"1":__('Swap_limit_sell 1'),"2":__('Swap_limit_sell 2')}, formatter: Table.api.formatter.normal},
                        {field: 'swap_market_buy', title: __('Swap_market_buy'), searchList: {"1":__('Swap_market_buy 1'),"2":__('Swap_market_buy 2')}, formatter: Table.api.formatter.normal},
                        {field: 'swap_market_sell', title: __('Swap_market_sell'), searchList: {"1":__('Swap_market_sell 1'),"2":__('Swap_market_sell 2')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
         //手续费设置
        handlingset: function () {
            // 不可见元素不验证
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});