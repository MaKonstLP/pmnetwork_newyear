<?php

namespace frontend\modules\gorko_ny\models;

use common\models\RestaurantsTypes;
use common\models\Slices;
use Yii;

/**
 * This is the model class for table "restaurant_type_slice".
 *
 * @property int $restaurant_type_value
 * @property int $slice_id
 */
class RestaurantTypeSlice extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'restaurant_type_slice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['restaurant_type_value', 'slice_id'], 'required'],
            [['restaurant_type_value', 'slice_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'restaurant_type_value' => 'Restaurant Type Value',
            'slice_id' => 'Slice ID',
        ];
    }

    public function getSlice()
    {
        return $this->hasOne(Slices::class, ['id' => 'slice_id']);
    }

    public function getRestaurantType()
    {
        return $this->hasOne(RestaurantsTypes::class, ['value' => 'restaurant_type_value']);
    }
}
