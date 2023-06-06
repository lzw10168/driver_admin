define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'real_verified/index' + location.search,
                    add_url: 'real_verified/add',
                    edit_url: 'real_verified/edit',
                    del_url: 'real_verified/del',
                    multi_url: 'real_verified/multi',
                    import_url: 'real_verified/import',
                    table: 'real_verified',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'user.username', title: __('User.username')},
                        {field: 'user.mobile', title: __('User.mobile')},
                        {field: 'truename', title: __('Truename'), operate: 'LIKE'},
                        {field: 'idcard', title: __('Idcard'), operate: 'LIKE'},
                        {field: 'front_card_image', title: __('Front_card_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'back_card_image', title: __('Back_card_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), searchList: {"-1":__('Status -1'),"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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