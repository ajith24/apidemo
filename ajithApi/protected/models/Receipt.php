<?php

class Receipt extends CActiveRecord
{
	/**
	 * The followings are the available columns in table 'product':
	 * @var integer $id
	 * @var integer $rid
	 * @var string $product_id
	 * @var string $product_total
	 * @var string $product_discount
	 * @var integer $vat
	 * @var integer $total_price
	 * @var integer $created_at
	 * @var integer $updated_at
	 */
	const STATUS_DRAFT=1;
	const STATUS_PUBLISHED=2;
	const STATUS_ARCHIVED=3;

	private $_oldTags;

	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'receipt';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('product_id, product_total, product_discount, vat, total_price,rid', 'required'),
		);
	}



	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'rid' => 'RId',
			'product_id' => 'product_id',
			'product_total' => 'product_total',
			'product_discount' => 'product_discount',
			'vat' => 'vat',
			'total_price' => 'total_price',
			'created_at' => 'Created',
			'updated_at' => 'Updated',
		);
	}

	
}
