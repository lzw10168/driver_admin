define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'driver_verified/index' + location.search,
                    add_url: 'driver_verified/add',
                    edit_url: 'driver_verified/edit',
                    del_url: 'driver_verified/del',
                    multi_url: 'driver_verified/multi',
                    import_url: 'driver_verified/import',
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
                        {field: 'sign_province_name', title: __('Sign_province')},
                        {field: 'sign_city_name', title: __('Sign_city')},
                        {field: 'province', title: __('Province')},
                        {field: 'city', title: __('City')},
                        {field: 'area', title: __('Area')},
                        {field: 'driver_license', title: __('Driver_license'), operate: 'LIKE'},
                        {field: 'driver_front_image', title: __('Driver_front_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'driver_back_image', title: __('Driver_back_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
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