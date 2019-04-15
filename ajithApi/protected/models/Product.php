<?php

class Product extends CActiveRecord
{
	/**
	 * The followings are the available columns in table 'product':
	 * @var integer $id
	 * @var string $name
	 * @var integer $cost
	 * @var integer $vat_class
	 * @var integer $barcode
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
		return 'product';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, cost, vat_class, barcode', 'required'),
		);
	}



	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'name' => 'Name',
			'cost' => 'Cost',
			'vat_class' => 'Vat Class',
			'barcode' => 'Barcode',
			'created_at' => 'Created',
			'updated_at' => 'Updated',
		);
	}

	
}
