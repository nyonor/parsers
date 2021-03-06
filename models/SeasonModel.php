<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 22.10.15
 * Time: 17:11
 */
class SeasonModel
{
	const WINTER = "зима";
	const SUMMER = "лето";
	const ALL_SEASONS = "всесезонка";

	/**
	 * Подпихни сюда строчку - метод создаст класс сезона
	 * ВНИМАНИЕ! Используй на свой страх и риск
	 * ибо этот метод конструировался для работы с обновлением НАШЕЙ номенклатуры
	 * TODO: можно формировать regex паттерн динамически с помощью доп аргумента
	 * @param $stringWithSeasonName string
	 * @return SeasonModel
	 */
	public static function Factory($stringWithSeasonName) {

		//var_dump($stringWithSeasonName, true);
//var_dump( $stringWithSeasonName);die;
		//print ($stringWithSeasonName);
		$match = "";
		preg_match('/([Зз][Ии][Мм][Нн])/isu',$stringWithSeasonName,$match);
		//var_dump($match);
		if (count($match) > 1 && $match[1] != null)
			return new SeasonModel(SeasonModel::WINTER);

		$match = null;
		preg_match('/([Лл][Ее][Тт][Нн])/isu',$stringWithSeasonName,$match);
		if (count($match) > 1 && $match[1] != null)
			return new SeasonModel(SeasonModel::SUMMER);

		$match = null;
		preg_match('/([Вв][Сс][Ее][Сс][Ее][Зз][Оо][Нн])/isu',$stringWithSeasonName,$match);
		if (count($match) > 1 && $match[1] != null)
			return new SeasonModel(SeasonModel::ALL_SEASONS);

		return null;
	}

	public function __construct($seasonName) {
		if ($seasonName != self::ALL_SEASONS && $seasonName != self::SUMMER && $seasonName != self::WINTER )
			throw new Exception("Use only constants from SeasonModel class!");

		$this->seasonName = $seasonName;
	}

	protected $seasonName;

	public function GetSeasonName() {
		return $this->seasonName;
	}

}