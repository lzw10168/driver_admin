define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'new_coupon/index' + location.search,
                    add_url: 'new_coupon/add',
                    edit_url: 'new_coupon/edit',
                    del_url: 'new_coupon/del',
                    multi_url: 'new_coupon/multi',
                    import_url: 'new_coupon/import',
                    table: 'coupon',
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
                        {field: 'coupon_type', title: __('Coupon_type'), searchList: {"1":__('Coupon_type 1'),"2":__('Coupon_type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'coupon_name', title: __('Coupon_name'), searchList: {"1":__('Coupon_name 1'),"2":__('Coupon_name 2')}, formatter: Table.api.formatter.normal},
                        {field: 'coupon_price', title: __('Coupon_price'), operate:'BETWEEN'},
                        {field: 'limit_price', title: __('Limit_price'), operate:'BETWEEN'},
                        {
                            field: 'expiration',
                            title: __('Expiration'),
                            operate: false,
                            formatter: function(value, data) {

                                return data.expiration + '天';

                            }
                        },
                        {field: 'coupon_status', title: __('Coupon_status'), searchList: {"0":__('Coupon_status 0'),"1":__('Coupon_status 1')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function (value, row, index) {
                                var that = $.extend({}, this);
                                var table = $(that.table).clone(true);
                                $(table).data("operate-del", null);
                                that.table = table;
                                return Table.api.formatter.operate.call(that, value, row, index);
                            }}
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
        issue_user: function () {
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