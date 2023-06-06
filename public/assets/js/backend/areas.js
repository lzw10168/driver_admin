define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'areas/index' + location.search,
                    add_url: 'areas/add',
                    edit_url: 'areas/edit',
                    del_url: 'areas/del',
                    multi_url: 'areas/multi',
                    import_url: 'areas/import',
                    table: 'areas',
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
                        {field: 'parent_id', title: __('Parent_id')},
                        {field: 'level_type', title: __('Level_type')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'short_name', title: __('Short_name'), operate: 'LIKE'},
                        {field: 'parent_path', title: __('Parent_path'), operate: 'LIKE'},
                        {field: 'province', title: __('Province'), operate: 'LIKE'},
                        {field: 'city', title: __('City'), operate: 'LIKE'},
                        {field: 'district', title: __('District'), operate: 'LIKE'},
                        {field: 'province_short_name', title: __('Province_short_name'), operate: 'LIKE'},
                        {field: 'city_short_name', title: __('City_short_name'), operate: 'LIKE'},
                        {field: 'district_short_name', title: __('District_short_name'), operate: 'LIKE'},
                        {field: 'province_pinyin', title: __('Province_pinyin'), operate: 'LIKE'},
                        {field: 'city_pinyin', title: __('City_pinyin'), operate: 'LIKE'},
                        {field: 'district_pinyin', title: __('District_pinyin'), operate: 'LIKE'},
                        {field: 'city_code', title: __('City_code'), operate: 'LIKE'},
                        {field: 'zip_code', title: __('Zip_code'), operate: 'LIKE'},
                        {field: 'pinyin', title: __('Pinyin'), operate: 'LIKE'},
                        {field: 'jianpin', title: __('Jianpin'), operate: 'LIKE'},
                        {field: 'firstchar', title: __('Firstchar'), operate: 'LIKE'},
                        {field: 'lng', title: __('Lng'), operate: 'LIKE'},
                        {field: 'lat', title: __('Lat'), operate: 'LIKE'},
                        {field: 'remark1', title: __('Remark1'), operate: 'LIKE'},
                        {field: 'remark2', title: __('Remark2'), operate: 'LIKE'},
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