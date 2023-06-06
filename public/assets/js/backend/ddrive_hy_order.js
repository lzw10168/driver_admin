define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ddrive_hy_order/index' + location.search,
                    add_url: 'ddrive_hy_order/add',
                    edit_url: 'ddrive_hy_order/edit',
                    del_url: 'ddrive_hy_order/del',
                    multi_url: 'ddrive_hy_order/multi',
                    import_url: 'ddrive_hy_order/import',
                    table: 'ddrive_hy_order',
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
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'car.car_name', title: __('Car.car_name')},
                        {field: 'driver.username', title: __('Cargo_driver_id')},

                        {field: 'type', title: __('Type'), searchList: {"1":__('Type 1'),"2":__('Type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'appointment_time', title: __('Appointment_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"-2":__('Status -2'),"-1":__('Status -1'),"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4'),"5":__('Status 5'),"6":__('Status 6'),"7":__('Status 7')}, formatter: Table.api.formatter.status},
                        {field: 'people_num', title: __('People_num')},
                        {field: 'order_price', title: __('Order_price'), operate:'BETWEEN'},
                        {field: 'discount_price', title: __('Discount_price'), operate:'BETWEEN'},
                        {field: 'platform_service_fee', title: __('Platform_service_fee'), operate:'BETWEEN'},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"1":__('Pay_type 1'),"2":__('Pay_type 2'),"3":__('Pay_type 3')}, formatter: Table.api.formatter.normal},
                        {field: 'pay_method', title: __('Pay_method'), searchList: {"1":__('Pay_method 1'),"2":__('Pay_method 2')}, formatter: Table.api.formatter.normal},
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