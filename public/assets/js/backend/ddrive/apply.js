define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ddrive/apply/index' + location.search,
                    add_url: 'ddrive/apply/add',
                    edit_url: 'ddrive/apply/edit',
                    del_url: 'ddrive/apply/del',
                    multi_url: 'ddrive/apply/multi',
                    table: 'apply',
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
                        {field: 'user.nickname', title: __('User.nickname')},
                        {field: 'user.mobile', title: __('User.mobile')},
                        {field: 'name', title: __('Name')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'driving_age', title: __('Driving_age')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'card_image', title: __('Card_image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'card_back_image', title: __('Card_back_image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'driver_image', title: __('Driver_image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), searchList: {"-1":__('Status -1'),"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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