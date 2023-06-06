define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cwmap/maplocation/index',
                    add_url: 'cwmap/maplocation/add',
                    edit_url: 'cwmap/maplocation/edit',
                    del_url: 'cwmap/maplocation/del',
                    table: 'map_location',
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
                        {field: 'id', title: __('Id'), visible: false, sortable: false},
                        {field: 'locationname', title: __('Locationname')},
                        {field: 'detailaddress', title: __('Detailaddress')},
                        {field: 'longitude', title: __('Longitude'), visible: false, sortable: false},
                        {field: 'latitude', title: __('Latitude'), visible: false, sortable: false},
                        {field: 'phone', title: __('Phone')},
                        {field: 'email', title: __('Email')},
                        {field: 'fax', title: __('Fax')},
                        {field: 'qq', title: __('Qq')},
                        {field: 'website', title: __('Website')},
                        {field: 'picture', title: __('Picture'), visible: false, sortable: false, formatter: Table.api.formatter.image},
                        {field: 'province', title: __('Province')},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
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
        map: function () {
            Form.api.bindevent($("form[role=form]"));
            require(['async!BMap3'], function () {
                // 更多文档可参考 http://lbsyun.baidu.com/jsdemo.htm
                // 百度地图API功能
                var map = new BMap.Map("allmap");
                var point = new BMap.Point(116.404, 39.915);//精度，纬度

                map.centerAndZoom(point, 20); //设置中心坐标点和级别
                var marker = new BMap.Marker(point);  // 创建标注
                map.addOverlay(marker);               // 将标注添加到地图中
                marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画

                map.enableDragging();   //开启拖拽
                //map.enableInertialDragging();   //开启惯性拖拽
                map.enableScrollWheelZoom(true); //是否允许缩放
                //map.centerAndZoom("上海",15); //根据城市名设定地图中心点

                function G(id) {
                    return document.getElementById(id);
                }

                var ac = new BMap.Autocomplete(//建立一个自动完成的对象
                        {"input": "searchaddress"
                            , "location": map
                        });

                ac.addEventListener("onhighlight", function (e) {  //鼠标放在下拉列表上的事件
                    var str = "";
                    var _value = e.fromitem.value;
                    var value = "";
                    if (e.fromitem.index > -1) {
                        value = _value.province + _value.city + _value.district + _value.street + _value.business;
                    }
                    str = "FromItem<br />index = " + e.fromitem.index + "<br />value = " + value;

                    value = "";
                    if (e.toitem.index > -1) {
                        _value = e.toitem.value;
                        value = _value.province + _value.city + _value.district + _value.street + _value.business;
                    }
                    str += "<br />ToItem<br />index = " + e.toitem.index + "<br />value = " + value;
                    G("searchResultPanel").innerHTML = str;
                });

                var myValue;
                ac.addEventListener("onconfirm", function (e) {    //鼠标点击下拉列表后的事件
                    var _value = e.item.value;
                    myValue = _value.province + _value.city + _value.district + _value.street + _value.business;
                    G("searchResultPanel").innerHTML = "onconfirm<br />index = " + e.item.index + "<br />myValue = " + myValue;

                    setPlace();
                });

                function setPlace() {
                    map.clearOverlays();    //清除地图上所有覆盖物
                    function myFun() {
                        var pp = local.getResults().getPoi(0).point;    //获取第一个智能搜索的结果
                        map.centerAndZoom(pp, 18);
                        map.addOverlay(new BMap.Marker(pp));    //添加标注
                    }
                    var local = new BMap.LocalSearch(map, {//智能搜索
                        onSearchComplete: myFun
                    });
                    local.search(myValue);
                }
                //单击获取点击的经纬度
                var geoc = new BMap.Geocoder();
                map.addEventListener("click", function (e) {
                    var pt = e.point;
                    geoc.getLocation(pt, function (rs) {
                        var addComp = rs.addressComponents;
                        Layer.alert(addComp.province + ", " + addComp.city + ", " + addComp.district + ", " + addComp.street + ", " + addComp.streetNumber + " <br> "
                                + __('Longitude') + ' : ' + e.point.lng + ' , ' + __('Latitude') + ' : ' + e.point.lat);
//                        console.log(addComp.province + ", " + addComp.city + ", " + addComp.district + ", " + addComp.street + ", " + addComp.streetNumber + " <br> "
//                                + __('Longitude') + ' : ' + e.point.lng + ' , ' + __('Latitude') + ' : ' + e.point.lat);

                    });
                });
//
//                // 点搜索按钮时解析地址坐标
//                $(document).on('click', '.btn-search', function () {
//                    var local = new BMap.LocalSearch(map, {
//                        renderOptions: {map: map}
//                    });
//                    var searchkeyword = $("#searchaddress").val();
//                    local.search(searchkeyword);
//                });

            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                var default_zoom = 10;
                var find_zoom = 18;
                require(['async!BMap3'], function () {

                    var longitude = $("#c-longitude").val();
                    var latitude = $("#c-latitude").val();
                    // 百度地图API功能
                    var map = new BMap.Map("allmap");
                    var point;
                    if (longitude == "") {
                        point = new BMap.Point(116.404, 39.915);
                        map.centerAndZoom(point, default_zoom);
                    } else {
                        point = new BMap.Point(longitude, latitude);
                        map.centerAndZoom(point, find_zoom);
                        marker = new BMap.Marker(point);  // 创建标注
                        map.addOverlay(marker);               // 将标注添加到地图中
                        marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画
                    }

                    var top_left_navigation = new BMap.NavigationControl();  //左上角，添加默认缩放平移控件
                    map.addControl(top_left_navigation);

                    // 创建地址解析器实例
                    var myGeo = new BMap.Geocoder();
                    map.addEventListener("click", function (e) {
                        var pt = e.point;
                        myGeo.getLocation(pt, function (rs) {
                            var addComp = rs.addressComponents;
                            $("#c-longitude").val(pt.lng);
                            $("#c-latitude").val(pt.lat);
                            $("#c-province").val(addComp.province);
                            Layer.msg(__('Position update') + ' <br> ' + __('Longitude') + ' : ' + pt.lng + ' , ' + __('Latitude') + ' : ' + pt.lat);

                            map.clearOverlays();
                            marker = new BMap.Marker(pt);  // 创建标注
                            map.addOverlay(marker);               // 将标注添加到地图中
                            marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画
                        });
                    });

                    function G(id) {
                        return document.getElementById(id);
                    }

                    var ac = new BMap.Autocomplete(//建立一个自动完成的对象
                            {"input": "searchaddress"
                                , "location": map
                            });

                    var myValue;
                    ac.addEventListener("onconfirm", function (e) {    //鼠标点击下拉列表后的事件
                        var _value = e.item.value;
                        myValue = _value.province + _value.city + _value.district + _value.street + _value.business;
                        G("searchResultPanel").innerHTML = "onconfirm<br />index = " + e.item.index + "<br />myValue = " + myValue;

                        setPlace();
                    });

                    function setPlace() {
                        map.clearOverlays();    //清除地图上所有覆盖物
                        function myFun() {
                            var pp = local.getResults().getPoi(0).point;    //获取第一个智能搜索的结果
                            myGeo.getLocation(pp, function (rs) {
                                var addComp = rs.addressComponents;
                                $("#c-longitude").val(pp.lng);
                                $("#c-latitude").val(pp.lat);
                                $("#c-province").val(addComp.province);
                                map.centerAndZoom(pp, find_zoom);
                                marker = new BMap.Marker(pp);  // 创建标注
                                map.addOverlay(marker);               // 将标注添加到地图中
                                marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画
                                Layer.msg(__('Position update') + ' <br> ' + __('Longitude') + ' : ' + pp.lng + ' , ' + __('Latitude') + ' : ' + pp.lat);
                            });
                        }
                        var local = new BMap.LocalSearch(map, {//智能搜索
                            onSearchComplete: myFun
                        });
                        local.search(myValue);
                    }
                })

            }
        }
    };
    return Controller;
});