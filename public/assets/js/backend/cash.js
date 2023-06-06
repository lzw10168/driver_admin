define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cash/index' + location.search,
                    add_url: 'cash/add',
                    edit_url: 'cash/edit',
                    del_url: 'cash/del',
                    multi_url: 'cash/multi',
                    import_url: 'cash/import',
                    table: 'cash',
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
                        {field: 'user.mobile', title: __('User.mobile')},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"1":__('Pay_type 1'),"2":__('Pay_type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}, formatter: Table.api.formatter.status},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'actual_payment', title: __('Actual_payment'), operate:'BETWEEN'},
                        {field: 'commission', title: __('Commission'), operate:'BETWEEN'},
                        {field: 'account_number', title: __('Account_number'), operate: 'LIKE'},
                        {field: 'payee', title: __('Payee'), operate: 'LIKE'},
                        // {field: 'bank_name', title: __('Bank_name'), operate: 'LIKE'},
                        {field: 'admin.username', title: __('Admin_id')},
                        {field: 'failure_cause', title: __('Failure_cause'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});