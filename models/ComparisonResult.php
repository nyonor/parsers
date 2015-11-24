<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 28.10.15
 * Time: 16:08
 */
class ComparisonResult extends ProductTireModel
{
	/**
	 * Если это свойство выставлено в true, то данный товар требует сверки оператором!
	 * так как название модели у конкурента не точно совпадает с названием модели в нашей номенклатуре!!!
	 * @var boolean
	 */
	public $shouldCheckByOperator;
	public $relevanceModel;
	public $relevanceBrand;
	public $rivalParsedId;
	public $quantity;
	public $conModel;
	public $conBrand;

}