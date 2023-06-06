define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ddrive/withdraw/index' + location.search,
                    add_url: 'ddrive/withdraw/add',
                    edit_url: 'ddrive/withdraw/edit',
                    del_url: 'ddrive/withdraw/del',
                    multi_url: 'ddrive/withdraw/multi',
                    table: 'withdraw',
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
                        {field: 'user.nickname', title: __('User.nickname')},
                        {field: 'user.mobile', title: __('User.mobile')},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"-1":__('Status -1')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: function(value, row, index){
                            var detail="";
                            if(row.status==0){
                                //待审核
                                detail+='<a class="btn btn-xs btn-danger btn-ajax" data-confirm="确定要拒绝该请求吗"  data-params="status=-1">拒绝</a> ';
                                detail+='<a class="btn btn-xs btn-success btn-ajax" data-confirm="确定要通过该请求吗"  data-params="status=1">通过</a> ';
                            }
                            if(row.status==1){
                                detail+='提现成功';
                            }
                            return detail;
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
            },
            events:{
                operate: $.extend({
                    'click .btn-ajax':function(e, value, row, index){
                        e.stopPropagation();
                        var that = this;
                        var confirm=$(this).data('confirm');
                        var index = Layer.confirm(
                                __(confirm),
                                {icon: 3, title: __('Warning'), shadeClose: true},
                                function () {
                                    Table.api.multi("", row['id'], $("#table"), that);
                                    Layer.close(index);
                                }
                        );
                    }
                }, Table.api.events.operate)
            }
        }
    };
    return Controller;
});