define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ddrive_sf_order_comment/index' + location.search,
                    add_url: 'ddrive_sf_order_comment/add',
                    edit_url: 'ddrive_sf_order_comment/edit',
                    del_url: 'ddrive_sf_order_comment/del',
                    multi_url: 'ddrive_sf_order_comment/multi',
                    import_url: 'ddrive_sf_order_comment/import',
                    table: 'ddrive_sf_order_comment',
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
                        {field: 'order_id', title: __('Order_id')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'user.mobile', title: __('User.mobile')},
                        {field: 'driver_id', title: __('Driver_id')},
                        {field: 'driver.mobile', title: __('Driver.mobile')},
                        {field: 'score', title: __('Score')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});