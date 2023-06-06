<?php

namespace app\admin\model;

use think\Model;


class Details extends Model
{

    

    

    // 表名
    protected $name = 'details';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'fluctuate_type_text',
        'assets_type_text',
        'source_type_text'
    ];
    

    
    public function getFluctuateTypeList()
    {
        return ['1' => __('Fluctuate_type 1')];
    }

    public function getAssetsTypeList()
    {
        return ['1' => __('Assets_type 1'), '2' => __('Assets_type 2')];
    }

    public function getSourceTypeList()
    {
        return ['1' => __('Source_type 1'), '2' => __('Source_type 2')];
    }


    public function getFluctuateTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['fluctuate_type']) ? $data['fluctuate_type'] : '');
        $list = $this->getFluctuateTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAssetsTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['assets_type']) ? $data['assets_type'] : '');
        $list = $this->getAssetsTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getSourceTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['source_type']) ? $data['source_type'] : '');
        $list = $this->getSourceTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function addDetail($user_id,$fluctuate_type,$msg,$money,$assets_type,$source_type,$form_id=0){
        $data = [
            'user_id' => $user_id,
            'fluctuate_type' => $fluctuate_type,
            'msg' => $msg,
            'amount' => $money,
            'assets_type' => $assets_type,
            'source_type' => $source_type,
            'createtime' => time(),
            'form_id'=> $form_id
        ];
        $this->save($data);
    }


}
