define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'driver_user/index' + location.search,
                    add_url: 'driver_user/add',
                    edit_url: 'driver_user/edit',
                    del_url: 'driver_user/del',
                    multi_url: 'driver_user/multi',
                    import_url: 'driver_user/import',
                    table: 'driver_verified',
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
                        {
                            field: 'status',
                            title: __('Status'),
                            align: 'center',
                            searchList: {"-1": __('No'), "1": __('Yes')},
                            table: table,
                            formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function (value, row, index) {
                        //         var that = $.extend({}, this);
                        //         var table = $(that.table).clone(true);
                        //         $(table).data("operate-del", null);
                        //         that.table = table;
                        //         return Table.api.formatter.operate.call(that, value, row, index);
                        //     }}
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