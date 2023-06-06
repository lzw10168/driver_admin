<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:88:"/www/wwwroot/website.lovexy.life/public/../application/admin/view/ddrive/order/edit.html";i:1616820714;s:75:"/www/wwwroot/website.lovexy.life/application/admin/view/layout/default.html";i:1616820714;s:72:"/www/wwwroot/website.lovexy.life/application/admin/view/common/meta.html";i:1616820712;s:74:"/www/wwwroot/website.lovexy.life/application/admin/view/common/script.html";i:1616820712;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<meta name="referrer" content="never">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<?php if(\think\Config::get('fastadmin.adminskin')): ?>
<link href="/assets/css/skins/<?php echo \think\Config::get('fastadmin.adminskin'); ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
<?php endif; ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>

    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav') && \think\Config::get('fastadmin.breadcrumb')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <?php if($auth->check('dashboard')): ?>
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                    <?php endif; ?>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <form id="edit-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('User_id'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-user_id" data-rule="required" disabled="disabled" data-source="user/user/index" data-field="nickname" class="form-control selectpage" name="row[user_id]" type="text" value="<?php echo htmlentities($row['user_id']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Driver_id'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-driver_id" data-rule="required" data-source="user/user/index" data-field="nickname" class="form-control selectpage" name="row[driver_id]" type="text" value="<?php echo htmlentities($row['driver_id']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Type'); ?>:</label>
        <div class="col-xs-12 col-sm-8">

                <div class="radio">
                    <?php if(is_array($typeList) || $typeList instanceof \think\Collection || $typeList instanceof \think\Paginator): if( count($typeList)==0 ) : echo "" ;else: foreach($typeList as $key=>$vo): ?>
                    <label for="row[status]-<?php echo $key; ?>"><input id="row[type]-<?php echo $key; ?>" name="row[type]" type="radio" value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['type'])?$row['type']:explode(',',$row['type']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Appointment_time'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-appointment_time" data-rule="required" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true"  name="row[appointment_time]" type="text" value="<?php echo datetime($row['appointment_time']); ?>">


        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Start_city'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class='control-relative'><input id="c-start_city" data-rule="required" class="form-control" name="row[start_city]" type="text" value="<?php echo htmlentities($row['start_city']); ?>"></div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Start'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-start" data-rule="required" class="form-control" name="row[start]" type="text" value="<?php echo htmlentities($row['start']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Map'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <span id ="start" class="btn btn-success" data-toggle="addresspicker">选择地图出发详细位置</span>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Start_address'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-start_address" data-rule="required" class="form-control" name="row[start_address]" type="text" value="<?php echo htmlentities($row['start_address']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Start_latitude'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-start_latitude" data-rule="required" class="form-control" name="row[start_latitude]" type="text" value="<?php echo htmlentities($row['start_latitude']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Start_longitude'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-start_longitude" data-rule="required" class="form-control" name="row[start_longitude]" type="text" value="<?php echo htmlentities($row['start_longitude']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('End'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-end" data-rule="required" class="form-control" name="row[end]" type="text" value="<?php echo htmlentities($row['end']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('End_city'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class='control-relative'><input id="c-end_city" data-rule="required" class="form-control" name="row[end_city]" type="text" value="<?php echo htmlentities($row['end_city']); ?>"></div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Map'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <span id="end" class="btn btn-success" data-toggle="addresspicker">选择地图目的地地址</span>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('End_address'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-end_address" data-rule="required" class="form-control" name="row[end_address]" type="text" value="<?php echo htmlentities($row['end_address']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('End_latitude'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-end_latitude" data-rule="required" class="form-control" name="row[end_latitude]" type="text" value="<?php echo htmlentities($row['end_latitude']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('End_longitude'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-end_longitude" data-rule="required" class="form-control" name="row[end_longitude]" type="text" value="<?php echo htmlentities($row['end_longitude']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Distance'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-distance" class="form-control" step="0.01" name="row[distance]" type="number" value="<?php echo htmlentities($row['distance']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Duration'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-duration" class="form-control" step="0.01" name="row[duration]" type="number" value="<?php echo htmlentities($row['duration']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Estimated_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-estimated_price" class="form-control" step="0.01" name="row[estimated_price]" type="number" value="<?php echo htmlentities($row['estimated_price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-price" class="form-control" step="0.01" name="row[price]" type="number" value="<?php echo htmlentities($row['price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Status'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            
            <div class="radio">
            <?php if(is_array($statusList) || $statusList instanceof \think\Collection || $statusList instanceof \think\Paginator): if( count($statusList)==0 ) : echo "" ;else: foreach($statusList as $key=>$vo): ?>
            <label for="row[status]-<?php echo $key; ?>"><input id="row[status]-<?php echo $key; ?>" name="row[status]" type="radio" value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['status'])?$row['status']:explode(',',$row['status']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label> 
            <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Comment'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
                        
            <select  id="c-comment" data-rule="required" class="form-control selectpicker" name="row[comment]">
                <?php if(is_array($commentList) || $commentList instanceof \think\Collection || $commentList instanceof \think\Paginator): if( count($commentList)==0 ) : echo "" ;else: foreach($commentList as $key=>$vo): ?>
                    <option value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['comment'])?$row['comment']:explode(',',$row['comment']))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
