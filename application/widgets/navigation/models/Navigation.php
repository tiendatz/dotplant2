<?php

namespace app\widgets\navigation\models;

use app\backgroundtasks\traits\SearchModelTrait;
use app\behaviors\Tree;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "navigation".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property string $url
 * @property string $route
 * @property string $route_params
 * @property string $advanced_css_class
 * @property integer $sort_order
 * @property Navigation[] $children
 * @property Navigation $parent
 */
class Navigation extends \yii\db\ActiveRecord
{
    use SearchModelTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%navigation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'name'], 'required', 'except' => ['search']],
            [['parent_id', 'sort_order'], 'integer'],
            [['url', 'route', 'route_params', 'advanced_css_class'], 'string'],
            [['name'], 'string', 'max' => 80]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'name' => Yii::t('app', 'Name'),
            'url' => Yii::t('app', 'Url'),
            'route' => Yii::t('app', 'Route'),
            'route_params' => Yii::t('app', 'Route Params'),
            'advanced_css_class' => Yii::t('app', 'Advanced Css Class'),
            'sort_order' => Yii::t('app', 'Sort Order'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
            [
                'class' => Tree::className(),
                'sortOrder' => 'sort_order ASC'
            ],
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['parent_id', 'name', 'url', 'route', 'route_params', 'advanced_css_class', 'sort_order'],
            'search' => ['parent_id', 'name', 'url', 'route', 'route_params', 'advanced_css_class', 'sort_order'],
        ];
    }

    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );
        $query->andWhere(['parent_id' => $this->parent_id]);
        /* @var \yii\db\ActiveRecord|SearchModelTrait $this */
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $this->addCondition($query, Navigation::tableName(), 'name', true);
        $this->addCondition($query, Navigation::tableName(), 'url', true);
        $this->addCondition($query, Navigation::tableName(), 'route', true);
        $this->addCondition($query, Navigation::tableName(), 'route_params', true);
        return $dataProvider;
    }

    public function getChildren()
    {
        return $this->hasMany(Navigation::className(), ['parent_id'=>'id']);
    }
}
