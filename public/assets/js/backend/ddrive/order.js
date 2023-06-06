define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ddrive/order/index' + location.search,
                    add_url: 'ddrive/order/add',
                    edit_url: 'ddrive/order/edit',
                    del_url: 'ddrive/order/del',
                    multi_url: 'ddrive/order/multi',
                    table: 'order',
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
                        {field: 'user.username', title: __('User.username')},
                        {field: 'mobile', title: __('User.mobile')},
                        {field: 'driver.username', title: __('Driver_id')},
                        {field: 'driver.mobile', title: __('Driver.mobile')},
                        {field: 'start_address', title: __('Start_address')},
                        {field: 'end_address', title: __('End_address')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'platform_service_fee', title: __('Platform_service_fee'), operate:'BETWEEN'},
                        {field: 'status', title: __('Status'), searchList: {"-2":__('Status -2'),"-1":__('Status -1'),"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4'),"99":__('Status 99')}, formatter: Table.api.formatter.status},
                        {field: 'cancel_type', title: __('Cancel_type'), searchList: {"0":__('Cancel_type 0'),"1":__('Cancel_type 1'),"2":__('Cancel_type 2'),"3":__('Status 3'),"4":__('Status 4'),"5":__('Status 5'),"6":__('Status 6'),"7":__('Status 7'),"8":__('Status 8'),"9":__('Status 9'),"10":__('Status 10'),"11":__('Status 11'),"12":__('Status 12')}, formatter: Table.api.formatter.status},
                        // {field: 'comment', title: __('Comment'), searchList: {"0":__('Comment 0'),"1":__('Comment 1')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
            $("#start").data("callback", function(res){
                console.log(res)
                $("#c-start_address").val(res.address);
                $("#c-start_latitude").val(res.lat);
                $("#c-start_longitude").val(res.lng);
                //其中res则是包含了address/lat/lng等信息的JSON对象
            });
            $("#end").data("callback", function(res){
                console.log(res)
                $("#c-end_address").val(res.address);
                $("#c-end_latitude").val(res.lat);
                $("#c-end_longitude").val(res.lng);
                //其中res则是包含了address/lat/lng等信息的JSON对象
            });
            Controller.api.bindevent();
        },
        edit: function () {
            $("#start").data("callback", function(res){
                console.log(res)
                $("#c-start_address").val(res.address);
                $("#c-start_latitude").val(res.lat);
                $("#c-start_longitude").val(res.lng);
                //其中res则是包含了address/lat/lng等信息的JSON对象
            });
            $("#end").data("callback", function(res){
                console.log(res)
                $("#c-end_address").val(res.address);
                $("#c-end_latitude").val(res.lat);
                $("#c-end_longitude").val(res.lng);
                //其中res则是包含了address/lat/lng等信息的JSON对象
            });
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