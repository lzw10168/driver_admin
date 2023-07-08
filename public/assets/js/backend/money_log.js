define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'money_log/index' + location.search,
                }
            });

            var table = $("#table");

            // 初始化表格
            // id
            // user_id
            // driver_name
            // money
            // before
            // after
            // memo
            // createtime
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: '用户ID'},
                        {field: 'driver_name', title: '司机姓名', operate: 'LIKE'},
                        {field: 'mobile', title: '司机手机号', operate: 'LIKE'},

                        {field: 'money', title: '金额'},
                        {field: 'before', title: '变动前金额'},
                        {field: 'after', title: '变动后金额'},
                        {field: 'memo', title: '备注'},
                        {field: 'createtime', title: '创建时间', formatter: Table.api.formatter.datetime},
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
