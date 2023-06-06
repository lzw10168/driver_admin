define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'new_info/index' + location.search,
                    add_url: 'new_info/add',
                    edit_url: 'new_info/edit',
                    del_url: 'new_info/del',
                    multi_url: 'new_info/multi',
                    import_url: 'new_info/import',
                    table: 'new_info',
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
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'keywords', title: __('Keywords'), operate: 'LIKE'},
                        {field: 'abstract', title: __('Abstract'), operate: 'LIKE'},
                        {field: 'author', title: __('Author'), operate: 'LIKE'},
                        {field: 'view_num', title: __('View_num')},
                        {field: 'new_info_img', title: __('New_info_img'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'new.name', title: __('Category_id')},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
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