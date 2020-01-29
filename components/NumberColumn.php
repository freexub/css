<?php
/**
 * Created by PhpStorm.
 * User: k.shtefan
 * Date: 15.10.2018
 * Time: 20:03
 */

namespace app\components;
use Yii;

use yii\grid\DataColumn;
use app\models\Data;
use app\models\DataCategory;

class NumberColumn extends DataColumn
{
    private $_total = 0;
    private $c;
    private $all ;
    private $id ;
    private $qwery ;

    public function getDataCellValue($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        /*
        $model = new Data();
        $parent = DataCategory::find()->select('id')->where(['parent_id'=>NULL])->all();
        for($j=0;count($parent)>$j;$j++) {
            $qwery = Data::find()->select('category_id')->where(['user_id' => Yii::$app->user->identity->id,'parent_id'=>$parent[$j]->id])->all();
            $c = 0;
            for ($i = 0; count($qwery) > $i; $i++) {
                $sum = (int)DataCategory::find()->where(['id' => $qwery[$i]->category_id])->sum('point');
                $c = $c + (int)$sum;
            }
            $this->all[] = $c;
        }
         if($value == 0){
            $value = '';
        }
        */
        $c = 0;
        $qwery = Data::find()->select('category_id')->where(['user_id' => $value])->all();
        for ($i = 0; count($qwery) > $i; $i++) {
            $sum = (int)DataCategory::find()->where(['id' => $qwery[$i]->category_id])->sum('point');
            $c = $c + (int)$sum;
        }
        $this->_total += $value;
        $id = $value;
        $value = $c.'=='.$id;
        return $value;
    }

    protected function renderFooterCellContent()
    {
        return $this->grid->formatter->format('<b>'.$this->_total.'</b>', $this->format);
        #return $this->grid->formatter->format('<b>'.$this->all[0].'</b>', $this->format);
    }
}