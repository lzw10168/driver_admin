define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ddrive_sf_order/index' + location.search,
                    add_url: 'ddrive_sf_order/add',
                    edit_url: 'ddrive_sf_order/edit',
                    del_url: 'ddrive_sf_order/del',
                    multi_url: 'ddrive_sf_order/multi',
                    import_url: 'ddrive_sf_order/import',
                    table: 'ddrive_sf_order',
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
                        {field: 'order_type', title: __('Order_type'), searchList: {"1":__('Order_type 1'),"2":__('Order_type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'user.username', title: __('User.username')},
                        {field: 'tel', title: __('Tel'), operate: 'LIKE'},
                        {field: 'other_user_id', title: __('Other_user_id')},
                        {field: 'driver.username', title: __('Driver.username')},
                        {field: 'driver.mobile', title: __('Driver.mobile')},
                        //{field: 'tel', title: __('Tel'), operate: 'LIKE'},
                        {field: 'order_money', title: __('Order_money'), operate:'BETWEEN'},
                        {field: 'platform_service_fee', title: __('Platform_service_fee'), operate:'BETWEEN'},

                        // {field: 'start_latitude', title: __('Start_latitude'), operate: 'LIKE'},
                        // {field: 'start_longitude', title: __('Start_longitude'), operate: 'LIKE'},
                        // {field: 'start_city', title: __('Start_city'), operate: 'LIKE'},
                        {field: 'start_address', title: __('Start_address'), operate: 'LIKE'},
                        // {field: 'start_name', title: __('Start_name'), operate: 'LIKE'},
                        // {field: 'end_latitude', title: __('End_latitude'), operate: 'LIKE'},
                        // {field: 'end_longitude', title: __('End_longitude'), operate: 'LIKE'},
                        // {field: 'end_city', title: __('End_city'), operate: 'LIKE'},
                        {field: 'end_address', title: __('End_address'), operate: 'LIKE'},
                        // {field: 'end_name', title: __('End_name'), operate: 'LIKE'},
                        // {field: 'route', title: __('Route'), operate: 'LIKE'},

                        // {field: 'car_type', title: __('Car_type'), operate: 'LIKE'},
                        // {field: 'more_seats', title: __('More_seats')},
                        // {field: 'people_num', title: __('People_num')},
                        // {field: 'car_price', title: __('Car_price'), operate:'BETWEEN'},

                        // {field: 'pid', title: __('Pid')},
                        {field: 'status', title: __('Status'), searchList: {"-2":__('Status -2'),"-1":__('Status -1'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4'),"5":__('Status 5')}, formatter: Table.api.formatter.status},
                        {field: 'cancel_type', title: __('Cancel_type'), searchList: {" 1":__('Cancel_type  1'),"2":__('Cancel_type 2'),"3":__('Cancel_type 3'),"4":__('Cancel_type 4'),"5":__('Cancel_type 5'),"6":__('Cancel_type 6'),"7":__('Cancel_type 7'),"8":__('Cancel_type 8'),"9":__('Cancel_type 9'),"10":__('Cancel_type 10'),"11":__('Cancel_type 11'),"12":__('Cancel_type 12')}, formatter: Table.api.formatter.normal},
                        // {field: 'assess', title: __('Assess'), searchList: {"0":__('Assess 0'),"1":__('Assess 1')}, formatter: Table.api.formatter.normal},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"1":__('Pay_type 1'),"2":__('Pay_type 2'),"3":__('Pay_type 3')}, formatter: Table.api.formatter.normal},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1')}, formatter: Table.api.formatter.status},
                        {field: 'start_time', title: __('Start_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'cancel_time', title: __('Cancel_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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