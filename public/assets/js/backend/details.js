define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'details/index' + location.search,
                    add_url: 'details/add',
                    edit_url: 'details/edit',
                    del_url: 'details/del',
                    multi_url: 'details/multi',
                    import_url: 'details/import',
                    table: 'details',
                }
            });

            var table = $("#table");
            table.on('load-success.bs.table', function (e, data) {
              //这里可以获取从服务端获取的JSON数据
              var price = 0;
              data.rows.forEach(function (item) {
                price += +item.amount;
              });
              //这里我们手动设置底部的值
              $("#money").text(parseInt(price).toFixed(2));
            });
            // 初始化表格
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
                        {field: 'user_id', title: __('User_id')},
                        // {field: 'fluctuate_type', title: __('Fluctuate_type'), searchList: {"1":__('Fluctuate_type 1'), "2":__('Fluctuate_type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'msg', title: __('Msg'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount')},
                        // {field: 'assets_type', title: __('Assets_type'), searchList: {"1":__('Assets_type 1'),"2":__('Assets_type 2')}, formatter: Table.api.formatter.normal},
                        // {field: 'source_type', title: __('Source_type'), searchList: {"1":__('Source_type 1'),"2":__('Source_type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // 司机姓名
                        {field: 'driver_name', title: '司机姓名', operate: 'LIKE'},
                        {field: 'form_id', title: '订单编号'},
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
