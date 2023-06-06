<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:89:"/www/wwwroot/website.lovexy.life/public/../application/admin/view/market/handlingset.html";i:1616820714;s:75:"/www/wwwroot/website.lovexy.life/application/admin/view/layout/default.html";i:1616820714;s:72:"/www/wwwroot/website.lovexy.life/application/admin/view/common/meta.html";i:1616820712;s:74:"/www/wwwroot/website.lovexy.life/application/admin/view/common/script.html";i:1616820712;}*/ ?>
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
                                <style type="text/css">
    @media (max-width: 375px) {
        .edit-form tr td input {
            width: 100%;
        }

        .edit-form tr th:first-child,
        .edit-form tr td:first-child {
            width: 20%;
        }

        .edit-form tr th:last-child,
        .edit-form tr td:last-child {
            display: none;
        }
    }

    .cover_div {
        z-index: 19891015;
        background-color: rgb(0, 0, 0);
        opacity: 0.9;
    }

    .invite {
        width: 100%;
        height: 100%;
        color: #fff;
        position: relative;
        background-size: 100% auto;
        background-repeat: no-repeat;
    }

    .invite .poster-bg {
        width: 100%;
        height: 100%;
    }

    .invite .company {
        position: absolute;
        top: 55%;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        text-align: center;
        font-size: 12px;
    }

    .invite .company span {
        font-size: 18px;
    }

    .invite .qrcode {
        position: absolute;
        top: 63%;
        left: 50%;
        transform: translateX(-50%);
    }

    .invite .qrcode .code {
        width: 120px;
        height: 120px;
        border-radius: 4px;
    }

    .invite .qrcode .user {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30px;
        height: 30px;
        border: 1px solid #fff;
        border-radius: 2px;
        overflow: hidden;
    }

    .invite .msg {
        position: absolute;
        bottom: 5%;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        text-align: center;
        font-size: 10px;
    }

    .cert-wrap {
        position: relative;
        width: 375px;
        height: 532px;
        min-width: 375px;
    }

    .bg {
        width: 100%;
        pointer-events: none;
        user-select: none;
    }

    .word {
        position: absolute;
    }

    .el-form-item__content {
        display: flex;
        align-items: center;
    }

    .el-form-item__content > div {
        margin-right: 10px;
    }

    .vdr {
        position: absolute;
        box-sizing: border-box;
        cursor: move;
    }

    .vdr.active:before {
        content: '';
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        box-sizing: border-box;
        outline: 1px dashed #d6d6d6;
    }

    .vdr-stick {
        box-sizing: border-box;
        position: absolute;
        font-size: 1px;
        background: #ffffff;
        border: 1px solid #6c6c6c;
        box-shadow: 0 0 2px #bbb;
    }

    .inactive .vdr-stick {
        display: none;
    }

    .vdr-stick-tl, .vdr-stick-br {
        cursor: nwse-resize;
    }

    .vdr-stick-tm, .vdr-stick-bm {
        left: 50%;
        cursor: ns-resize;
    }

    .vdr-stick-tr, .vdr-stick-bl {
        cursor: nesw-resize;
    }

    .vdr-stick-ml, .vdr-stick-mr {
        top: 50%;
        cursor: ew-resize;
    }

    .vdr-stick.not-resizable {
        display: none;
    }
</style>

<div class="panel panel-default panel-intro">
    <div class="panel-heading">
        <?php echo build_heading(null, false); ?>
        <ul class="nav nav-tabs">
            <li class="active"><a href="#basic" data-toggle="tab">车类开关设置</a></li>
        </ul>

    </div>

    <div id="myTabContent" class="tab-content">
        <div class="tab-pane fade active in" id="basic">
            <div class="widget-body no-padding">
                <form id="basic-form" class="edit-form form-horizontal" role="form" data-toggle="validator"
                      method="POST" action="<?php echo url('market/handlingset'); ?>">
                    <table class="table table-striped" style="border-collapse:separate; border-spacing:0px 1px;">

                        <tbody>
                        <tr>
                            <td width=25%>代驾开关</td>
                            <td>
                                <div class="row">
                                    <div class="col-sm-8 col-xs-12">
                                        <div class="radio">
                                            <label for="row[dj_status]-1"><input
                                                    id="row[dj_status]-1"
                                                    name="row[dj_status]" class="see_lower"
                                                    type="radio" value="1" <?php if(($row['dj_status'] ?? 0) == 1): ?>checked<?php endif; ?> />
                                                开启</label>
                                            <label for="row[dj_status]-0"><input
                                                    id="row[dj_status]-0"
                                                    name="row[dj_status]" class="see_lower"
                                                    type="radio" value="0" <?php if(($row['dj_status'] ?? 0) != 1): ?>checked<?php endif; ?> />
                                                关闭</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4"></div>
                                </div>
                            </td>

                        </tr>
                        <tr>
                            <td width=25%>顺风车开关</td>
                            <td>
                                <div class="row">
                                    <div class="col-sm-8 col-xs-12">
                                        <div class="radio">
                                            <label for="row[sf_status]-1"><input
                                                    id="row[sf_status]-1"
                                                    name="row[sf_status]" class="see_lower"
                                                    type="radio" value="1" <?php if(($row['sf_status'] ?? 0) == 1): ?>checked<?php endif; ?> />
                                                开启</label>
                                            <label for="row[sf_status]-0"><input
                                                    id="row[sf_status]-0"
                                                    name="row[sf_status]" class="see_lower"
                                                    type="radio" value="0" <?php if(($row['sf_status'] ?? 0) != 1): ?>checked<?php endif; ?> />
                                                关闭</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4"></div>
                                </div>
                            </td>

                        </tr>
                        <tr>
                            <td width=25%>货运开关</td>
                            <td>
                                <div class="row">
                                    <div class="col-sm-8 col-xs-12">
                                        <div class="radio">
                                            <label for="row[dc_status]-1"><input
                                                    id="row[dc_status]-1"
                                                    name="row[dc_status]" class="see_lower"
                                                    type="radio" value="1" <?php if(($row['dc_status'] ?? 0) == 1): ?>checked<?php endif; ?> />
                                                开启</label>
                                            <label for="row[dc_status]-0"><input
                                                    id="row[dc_status]-0"
                                                    name="row[dc_status]" class="see_lower"
                                                    type="radio" value="0" <?php if(($row['dc_status'] ?? 0) != 1): ?>checked<?php endif; ?> />
                                                关闭</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4"></div>
                                </div>
                            </td>

                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td></td>
                            <td>
                                <button type="submit" class="btn btn-success btn-embossed">确定</button>
                                <button type="reset" class="btn btn-default btn-embossed">重置</button>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

</div>

<script>

</script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
