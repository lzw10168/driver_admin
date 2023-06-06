<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:91:"/www/wwwroot/website.lovexy.life/public/../application/admin/view/driver_verified/edit.html";i:1616820714;s:75:"/www/wwwroot/website.lovexy.life/application/admin/view/layout/default.html";i:1616820714;s:72:"/www/wwwroot/website.lovexy.life/application/admin/view/common/meta.html";i:1616820712;s:74:"/www/wwwroot/website.lovexy.life/application/admin/view/common/script.html";i:1616820712;}*/ ?>
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
            <input id="c-user_id" data-rule="required" data-source="user/user/index" data-field="nickname" class="form-control selectpage" name="row[user_id]" type="text" value="<?php echo htmlentities($row['user_id']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Sign_province'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-sign_province" data-rule="required" data-source="areas/index" data-field="province" class="form-control selectpage" name="row[sign_province]" type="text" value="<?php echo htmlentities($row['sign_province']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Sign_city'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-sign_city" data-rule="required" data-source="areas/index" data-field="city" class="form-control selectpage" name="row[sign_city]" type="text" value="<?php echo htmlentities($row['sign_city']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Province'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-province" data-rule="required" data-source="areas/index" data-field="province" class="form-control selectpage" name="row[province]" type="text" value="<?php echo htmlentities($row['province']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('City'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-city" data-rule="required" data-source="areas/index" data-field="city" class="form-control selectpage" name="row[city]" type="text" value="<?php echo htmlentities($row['city']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Area'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-area" data-rule="required" data-source="areas/index" data-field="district" class="form-control selectpage" name="row[area]" type="text" value="<?php echo htmlentities($row['area']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Driver_license'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-driver_license" data-rule="required" class="form-control" name="row[driver_license]" type="text" value="<?php echo htmlentities($row['driver_license']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Driver_front_image'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-driver_front_image" data-rule="required" class="form-control" size="50" name="row[driver_front_image]" type="text" value="<?php echo htmlentities($row['driver_front_image']); ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-driver_front_image" class="btn btn-danger faupload" data-input-id="c-driver_front_image" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp" data-multiple="false" data-preview-id="p-driver_front_image"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-driver_front_image" class="btn btn-primary fachoose" data-input-id="c-driver_front_image" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right" for="c-driver_front_image"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-driver_front_image"></ul>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Driver_back_image'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-driver_back_image" data-rule="required" class="form-control" size="50" name="row[driver_back_image]" type="text" value="<?php echo htmlentities($row['driver_back_image']); ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-driver_back_image" class="btn btn-danger faupload" data-input-id="c-driver_back_image" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp" data-multiple="false" data-preview-id="p-driver_back_image"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-driver_back_image" class="btn btn-primary fachoose" data-input-id="c-driver_back_image" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right" for="c-driver_back_image"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-driver_back_image"></ul>
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
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Fail_reason'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-fail_reason" class="form-control " rows="5" name="row[fail_reason]" cols="50"><?php echo htmlentities($row['fail_reason']); ?></textarea>
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
