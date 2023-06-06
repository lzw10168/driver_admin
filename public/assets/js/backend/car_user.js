define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'car_user/index' + location.search,
                    add_url: 'car_user/add',
                    edit_url: 'car_user/edit',
                    del_url: 'car_user/del',
                    multi_url: 'car_user/multi',
                    import_url: 'car_user/import',
                    table: 'card_verified',
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
                        // {field: 'sign_province_name', title: __('Sign_province')},
                        // {field: 'sign_city_name', title: __('Sign_city')},
                        // {field: 'province', title: __('Province')},
                        // {field: 'city', title: __('City')},
                        // {field: 'area', title: __('Area')},
                        // {field: 'card_brand', title: __('Card_brand'), operate: 'LIKE'},
                        // {field: 'number_plate', title: __('Number_plate'), operate: 'LIKE'},
                        // {field: 'card_front_image', title: __('Card_front_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'card_back_image', title: __('Card_back_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {
                            field: 'status',
                            title: __('Status'),
                            align: 'center',
                            searchList: {"-1": __('No'), "1": __('Yes')},
                            table: table,
                            formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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