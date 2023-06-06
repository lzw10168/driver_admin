define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ddrive_refund/index' + location.search,
                    add_url: 'ddrive_refund/add',
                    edit_url: 'ddrive_refund/edit',
                    del_url: 'ddrive_refund/del',
                    multi_url: 'ddrive_refund/multi',
                    import_url: 'ddrive_refund/import',
                    table: 'ddrive_refund',
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
                        {field: 'user.username', title: __('User.username'), operate: 'LIKE'},
                        {field: 'order_id', title: __('Order_id')},
                        {field: 'order_number', title: __('Order_number'), operate: 'LIKE'},
                        {field: 'number', title: __('Number'), operate: 'LIKE'},
                        {field: 'refund_number', title: __('Refund_number'), operate: 'LIKE'},
                        {field: 'account', title: __('Account'), operate: 'LIKE'},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"1":__('Pay_type 1'),"2":__('Pay_type 2'),"3":__('Pay_type 3')}, formatter: Table.api.formatter.normal},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'check_status', title: __('Check_status'), searchList: {"0":__('Check_status 0'),"1":__('Check_status 1'),"2":__('Check_status 2'),"-1":__('Check_status -1')}, formatter: Table.api.formatter.status},
                        {field: 'apply_money', title: __('Apply_money'), operate:'BETWEEN'},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'apply_time', title: __('Apply_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'confirm_time', title: __('Confirm_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'success_time', title: __('Success_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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