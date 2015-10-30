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

	/**
	 * @var RivalTireModel|StdClass TODO: если будем искать еще и диски, соответственно надо будет менять
	 */
	public $rivalModel;
	public $relevanceModel;
	public $relevanceBrand;
	public $rivalParsedId;
}