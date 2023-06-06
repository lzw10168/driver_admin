define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ddrive_hy_freight/index' + location.search,
                    add_url: 'ddrive_hy_freight/add',
                    edit_url: 'ddrive_hy_freight/edit',
                    del_url: 'ddrive_hy_freight/del',
                    multi_url: 'ddrive_hy_freight/multi',
                    import_url: 'ddrive_hy_freight/import',
                    table: 'ddrive_hy_freight',
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
                        {field: 'car_name', title: __('Car_name'), operate: 'LIKE'},
                        {field: 'car_image', title: __('Car_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'load', title: __('Load'), operate: 'LIKE'},
                        {field: 'length', title: __('Length'), operate:'BETWEEN'},
                        {field: 'width', title: __('Width'), operate:'BETWEEN'},
                        {field: 'height', title: __('Height'), operate:'BETWEEN'},
                        {field: 'volume', title: __('Volume'), operate:'BETWEEN'},
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