<?php

namespace frontend\modules\gorko_ny\models;

use common\models\FilterItems;
use common\models\Subdomen;
use Yii;

/**
 * This is the model class for table "subdomen_filteritem".
 *
 * @property int $id
 * @property int $subdomen_id
 * @property int $filter_items_id
 * @property int|null $hits
 * @property int|null $is_valid
 * @property string $updated_at
 *
 * @property FilterItems $filterItems
 * @property Subdomen $subdomen
 */
class SubdomenFilteritem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subdomen_filteritem';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subdomen_id', 'filter_items_id'], 'required'],
            [['subdomen_id', 'filter_items_id', 'hits', 'is_valid'], 'integer'],
            [['subdomen_id', 'filter_items_id'], 'unique', 'targetAttribute' => ['subdomen_id', 'filter_items_id']],
            [['filter_items_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilterItems::className(), 'targetAttribute' => ['filter_items_id' => 'id']],
            [['subdomen_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subdomen::className(), 'targetAttribute' => ['subdomen_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subdomen_id' => 'Subdomen ID',
            'filter_items_id' => 'Filter Items ID',
            'hits' => 'Hits',
            'is_valid' => 'Is Valid',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[FilterItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilterItems()
    {
        return $this->hasOne(FilterItems::className(), ['id' => 'filter_items_id']);
    }

    /**
     * Gets query for [[Subdomen]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubdomen()
    {
        return $this->hasOne(Subdomen::className(), ['id' => 'subdomen_id']);
    }

    public static function deactivate()
    {
        return \Yii::$app->db->createCommand(
            "UPDATE subdomen_filteritem SET is_valid = 0"
        )->execute();
    }
}
